<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Generate referral code if user doesn't have one
        if (!$user->referral_code) {
            $user->update([
                'referral_code' => strtoupper(Str::random(8))
            ]);
        }

        if ($request->ajax()) {
            $query = $user->referrals()->select('users.*');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('user_info', function ($ref) {
                    $initial = substr($ref->name, 0, 1);
                    return '<div>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold me-2" style="width:30px;height:30px;font-size:0.75rem">' . $initial . '</div>
                            <div class="small fw-bold text-dark">' . $ref->name . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('date', function ($ref) {
                    return '<span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>' . $ref->created_at->format('d M Y') . '</span>';
                })
                ->addColumn('status_badge', function ($ref) {
                    if ($ref->is_active) {
                        return '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2" style="font-size: 0.7rem;">Aktif</span>';
                    } else {
                        return '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2" style="font-size: 0.7rem;">Tidak Aktif</span>';
                    }
                })
                ->rawColumns(['user_info', 'date', 'status_badge'])
                ->make(true);
        }

        $wallet = $user->wallet;
        $totalCommission = $wallet ? $wallet->transactions()
            ->where('type', 'deposit')
            ->where('status', 'completed')
            ->where('description', 'like', '%Komisi Affiliate%')
            ->sum('amount') : 0;

        $referralLink = route('home', ['ref' => $user->referral_code]);

        return view('theme::account.affiliate', compact('totalCommission', 'referralLink'));
    }
}
