<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('wallet')
                ->where('is_active', true)
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['admin', 'superadmin']);
                })
                ->select('users.*');

            return DataTables::of($query)
                ->addColumn('user_info', function ($user) {
                    $initial = substr($user->name, 0, 1);
                    return '
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold me-3"
                                style="width:36px;height:36px">' . $initial . '</div>
                            <div>
                                <h6 class="mb-0 fw-semibold">' . $user->name . '</h6>
                                <small class="text-muted">Klien ID: #' . $user->id . '</small>
                            </div>
                        </div>';
                })
                ->addColumn('contact', function ($user) {
                    return '
                        <div class="small">' . $user->email . '</div>
                        <div class="small text-muted">' . ($user->phone ?? '-') . '</div>';
                })
                ->addColumn('balance', function ($user) {
                    $bal = number_format($user->wallet->balance ?? 0, 0, ',', '.');
                    return '<span class="badge bg-success bg-opacity-10 text-success fs-6" style="border-radius:8px">Rp ' . $bal . '</span>';
                })
                ->addColumn('action', function ($user) {
                    $url = route('admin.wallets.show', $user->id);
                    return '<div class="d-flex gap-2 justify-content-end"><a href="' . $url . '" class="btn btn-sm btn-light text-primary rounded-pill px-3" style="font-size: 0.75rem;" title="Kelola Saldo"><i class="bi bi-wallet2 me-1"></i> Kelola Saldo</a></div>';
                })
                ->rawColumns(['user_info', 'contact', 'balance', 'action'])
                ->make(true);
        }

        $totalBalance = Wallet::sum('balance');
        return view('admin.wallets.index', compact('totalBalance'));
    }

    public function show(User $user)
    {
        // Ensure user has a wallet
        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        $transactions = $wallet->transactions()->latest()->paginate(15);

        return view('admin.wallets.show', compact('user', 'wallet', 'transactions'));
    }

    public function transaction(Request $request, User $user)
    {
        $request->validate([
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ($request->type === 'withdrawal' && $wallet->balance < $request->amount) {
            return back()->with('error', 'Saldo tidak mencukupi untuk penarikan.');
        }

        DB::transaction(function () use ($wallet, $request) {
            $reference = 'MNL-' . strtoupper(uniqid());

            if ($request->type === 'deposit') {
                $wallet->deposit($request->amount, $request->description, $reference, 'completed');
            } else {
                $wallet->withdraw($request->amount, $request->description, $reference, 'completed');
            }
        });

        $action = $request->type === 'deposit' ? 'Top Up' : 'Penarikan';
        return back()->with('success', "{$action} saldo sebesar Rp " . number_format($request->amount, 0, ',', '.') . " berhasil.");
    }

    public function requests(Request $request)
    {
        if ($request->ajax()) {
            $query = WalletTransaction::with('wallet.user')->select('wallet_transactions.*');

            if ($request->filled('status')) {
                $query->where('wallet_transactions.status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('wallet_transactions.type', $request->type);
            }

            return DataTables::of($query)
                ->addColumn('date', function ($trx) {
                    return '<span class="text-muted small">' . $trx->created_at->format('d M Y, H:i') . '</span>';
                })
                ->addColumn('ref_type', function ($trx) {
                    $ref = '<div class="fw-medium font-monospace small">' . $trx->reference_number . '</div>';
                    if ($trx->type === 'deposit') {
                        $ref .= '<span class="badge bg-primary bg-opacity-10 text-primary mt-1" style="border-radius:6px; font-weight:600;"><i class="bi bi-box-arrow-in-down"></i> Top Up</span>';
                    } else {
                        $ref .= '<span class="badge bg-warning bg-opacity-10 text-warning mt-1" style="border-radius:6px; font-weight:600;"><i class="bi bi-box-arrow-up"></i> Penarikan</span>';
                    }
                    return $ref;
                })
                ->addColumn('customer', function ($trx) {
                    $name = $trx->wallet->user->name ?? 'Pengguna Dihapus';
                    return '
                        <div class="fw-semibold">' . $name . '</div>
                        <div class="small text-muted text-truncate" style="max-width:200px" title="' . htmlspecialchars($trx->description) . '">' . htmlspecialchars($trx->description) . '</div>
                    ';
                })
                ->addColumn('amount', function ($trx) {
                    return '<div class="fw-bold text-end">Rp ' . number_format($trx->amount, 0, ',', '.') . '</div>';
                })
                ->addColumn('status_badge', function ($trx) {
                    if ($trx->status === 'completed') {
                        return '<span class="badge bg-success" style="border-radius:6px"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
                    } elseif ($trx->status === 'cancelled') {
                        return '<span class="badge bg-danger" style="border-radius:6px"><i class="bi bi-x-circle me-1"></i>Ditolak</span>';
                    } else {
                        return '<span class="badge bg-secondary" style="border-radius:6px"><i class="bi bi-clock-history me-1"></i>Menunggu</span>';
                    }
                })
                ->addColumn('action', function ($trx) {
                    $buttons = '';

                    // Add "Lihat Bukti" button if proof exists
                    if ($trx->proof_image) {
                        $proofUrl = asset($trx->proof_image); // Note: I removed storage/ as assets usually handle path mapping or it might already include it
                        $buttons .= '<button type="button" onclick="showProof(\'' . $proofUrl . '\')" class="btn btn-sm btn-light text-primary rounded-pill px-2 me-1" title="Lihat Bukti Transfer"><i class="bi bi-image"></i></button>';
                    }

                    if ($trx->status === 'pending') {
                        $url = route('admin.wallets.updateRequest', $trx->id);
                        $csrf = csrf_token();

                        $buttons .= '
                            <div class="d-inline-flex gap-1 justify-content-center">
                                <form id="approve-trx-' . $trx->id . '" action="' . $url . '" method="POST" class="d-inline">
                                    <input type="hidden" name="_token" value="' . $csrf . '">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="status" value="completed">
                                    <button type="button" onclick="swalConfirmSubmit(\'approve-trx-' . $trx->id . '\', \'Setujui Transaksi?\', \'Proses status transaksi ini menjadi Selesai?\', \'success\')" class="btn btn-sm btn-light text-success rounded-pill px-2" title="Setujui/Selesai"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <form id="reject-trx-' . $trx->id . '" action="' . $url . '" method="POST" class="d-inline">
                                    <input type="hidden" name="_token" value="' . $csrf . '">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="button" onclick="swalConfirmSubmit(\'reject-trx-' . $trx->id . '\', \'Tolak Transaksi?\', \'Tolak/Batalkan transaksi ini?\', \'warning\')" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Tolak"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </div>
                        ';
                    } else {
                        $buttons .= '<span class="text-muted small ms-2"><i class="bi bi-check2-all me-1"></i> Diproses</span>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center">' . $buttons . '</div>';
                })
                ->rawColumns(['date', 'ref_type', 'customer', 'amount', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.wallets.requests');
    }

    public function updateRequest(Request $request, WalletTransaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:completed,cancelled'
        ]);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        if ($request->status === 'completed' && $transaction->type === 'withdrawal') {
            if ($transaction->wallet->balance < $transaction->amount) {
                return back()->with('error', 'Saldo pengguna tidak mencukupi untuk menyetujui penarikan ini.');
            }
        }

        DB::transaction(function () use ($transaction, $request) {
            $transaction->update(['status' => $request->status]);

            if ($request->status === 'completed') {
                if ($transaction->type === 'deposit') {
                    $transaction->wallet->increment('balance', $transaction->amount);
                } else if ($transaction->type === 'withdrawal') {
                    $transaction->wallet->decrement('balance', $transaction->amount);
                }
            }
        });

        return back()->with('success', 'Status permintaan berhasil diperbarui.');
    }
}
