<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function orders(Request $request)
    {
        if ($request->ajax()) {
            $query = auth()->user()->orders()->with('items')->select('orders.*');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('order_info', function ($order) {
                    $statusColors = [
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'processing' => 'primary',
                        'shipped' => 'secondary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'dark'
                    ];
                    $badge = '<span class="badge bg-' . ($statusColors[$order->status] ?? 'secondary') . ' bg-opacity-10 text-' . ($statusColors[$order->status] ?? 'secondary') . ' rounded-pill px-2" style="font-size: 0.7rem;">' . __('order.status_' . $order->status) . '</span>';

                    return '<div>
                        <div class="fw-bold text-dark">' . $order->order_number . '</div>
                        <div class="small text-muted">' . $order->created_at->format('d/m/Y H:i') . '</div>
                        <div class="mt-1">' . $badge . '</div>
                    </div>';
                })
                ->addColumn('items_summary', function ($order) {
                    $item = $order->items->first();
                    $content = '<div class="small text-dark font-medium">' . ($item ? $item->product_name : '-') . '</div>';
                    if ($order->items->count() > 1) {
                        $content .= '<div class="small text-muted">+' . ($order->items->count() - 1) . ' produk lainnya</div>';
                    }
                    return $content;
                })
                ->addColumn('total_formatted', function ($order) {
                    return '<div class="fw-bold text-primary">Rp ' . number_format($order->total, 0, ',', '.') . '</div>';
                })
                ->addColumn('action', function ($order) {
                    $buttons = '<div class="d-flex gap-2">';
                    if ($order->status === 'pending' && $order->payment_status === 'unpaid') {
                        $buttons .= '<a href="' . route('orders.payment', $order->order_number) . '" class="btn btn-sm btn-primary rounded-pill px-3">Bayar</a>';
                    }
                    $buttons .= '<a href="' . route('orders.track', $order->order_number) . '" class="btn btn-sm btn-light text-primary rounded-pill px-3">Detail</a>';
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['order_info', 'items_summary', 'total_formatted', 'action'])
                ->make(true);
        }

        $orders = auth()->user()->orders()->with('items')->latest()->paginate(10);
        return view('theme::account.orders', compact('orders'));
    }

    public function profile()
    {
        return view('theme::account.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $settings = app(\App\Services\SettingService::class);
        if ($request->filled('phone')) {
            $request->merge(['phone' => $settings->formatPhone($request->phone)]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . auth()->id(),
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'phone.unique' => 'Nomor WhatsApp ini sudah digunakan oleh akun lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $data = $request->only('name', 'phone');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        auth()->user()->update($data);
        return redirect()->route('account.profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
