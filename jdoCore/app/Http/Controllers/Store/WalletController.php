<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ($request->ajax()) {
            $query = $wallet->transactions()->select('wallet_transactions.*');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('date', function ($trx) {
                    return '<div>
                        <div class="small fw-bold text-dark">' . $trx->created_at->format('d M Y') . '</div>
                        <div class="small text-muted">' . $trx->created_at->format('H:i') . '</div>
                    </div>';
                })
                ->addColumn('description_formatted', function ($trx) {
                    return '<div>
                        <div class="small text-dark">' . htmlspecialchars($trx->description) . '</div>
                        <span class="badge bg-light text-dark border mt-1" style="font-size:0.65rem;">' . $trx->reference_number . '</span>
                    </div>';
                })
                ->addColumn('status_badge', function ($trx) {
                    if ($trx->status === 'completed') {
                        return '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
                    } elseif ($trx->status === 'cancelled') {
                        return '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2" style="font-size: 0.7rem;"><i class="bi bi-x-circle me-1"></i>Ditolak</span>';
                    } else {
                        $html = '<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2" style="font-size: 0.7rem;"><i class="bi bi-clock-history me-1"></i>Diproses</span>';
                        if ($trx->type === 'deposit' && empty($trx->proof_image)) {
                            $url = route('account.wallet.payment', $trx->reference_number);
                            $html .= '<div class="mt-1"><a href="' . $url . '" class="btn btn-sm btn-outline-primary" style="font-size: 0.65rem; padding: 2px 6px;">Bayar Sekarang</a></div>';
                        }
                        return $html;
                    }
                })
                ->addColumn('amount_formatted', function ($trx) {
                    $color = $trx->type === 'deposit' ? 'success' : 'danger';
                    $prefix = $trx->type === 'deposit' ? '+' : '-';
                    return '<div class="fw-bold text-' . $color . '">
                        ' . $prefix . 'Rp ' . number_format($trx->amount, 0, ',', '.') . '
                    </div>';
                })
                ->rawColumns(['date', 'description_formatted', 'status_badge', 'amount_formatted'])
                ->make(true);
        }

        $bankAccounts = $user->bankAccounts()->get();
        return view('theme::account.wallet', compact('wallet', 'bankAccounts'));
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        $user = auth()->user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return back()->with('error', 'Saldo tidak mencukupi untuk penarikan.');
        }

        $bankAccount = $user->bankAccounts()->where('id', $request->bank_account_id)->firstOrFail();

        $reference = 'WDR-' . strtoupper(uniqid());
        $accountNumber = $bankAccount->account_number;
        $description = "Penarikan ke {$bankAccount->bank_name} - {$accountNumber} ({$bankAccount->account_name})";

        // Create a pending transaction
        $wallet->transactions()->create([
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'description' => $description,
            'reference_number' => $reference,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Permintaan penarikan berhasil dibuat. Menunggu persetujuan admin.');
    }

    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        $user = auth()->user();
        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        $reference = 'TUP-' . strtoupper(uniqid());
        $description = "Top Up Saldo - Menunggu Pembayaran";

        $transaction = $wallet->transactions()->create([
            'type' => 'deposit',
            'amount' => $request->amount,
            'description' => $description,
            'reference_number' => $reference,
            'status' => 'pending',
        ]);

        return redirect()->route('account.wallet.payment', $transaction->reference_number)
            ->with('success', 'Permintaan Top Up berhasil dibuat. Silakan selesaikan pembayaran.');
    }
    public function payment(string $referenceNumber)
    {
        $transaction = auth()->user()->wallet->transactions()
            ->where('reference_number', $referenceNumber)
            ->where('status', 'pending')
            ->firstOrFail();

        $bankAccountsRaw = app(\App\Services\SettingService::class)->get('bank_accounts', []);
        $bankAccounts = is_array($bankAccountsRaw) ? $bankAccountsRaw : (json_decode($bankAccountsRaw, true) ?: []);

        return view('theme::account.wallet-payment', compact('transaction', 'bankAccounts'));
    }

    public function uploadPayment(Request $request, string $referenceNumber)
    {
        $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'payment_method' => 'required|string',
        ]);

        $transaction = auth()->user()->wallet->transactions()
            ->where('reference_number', $referenceNumber)
            ->where('status', 'pending')
            ->firstOrFail();

        $file = $request->file('proof');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/payment-proofs'), $filename);
        $path = 'uploads/payment-proofs/' . $filename;

        $transaction->update([
            'payment_method' => $request->payment_method,
            'proof_image' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('account.wallet')
            ->with('success', 'Bukti transfer Top Up berhasil diunggah. Menunggu verifikasi admin.');
    }
}
