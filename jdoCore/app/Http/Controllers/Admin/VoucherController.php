<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Voucher::select('vouchers.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('code_badge', function ($voucher) {
                    return '<div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-ticket-perforated text-primary"></i> 
                                ' . $voucher->code . '
                            </div>';
                })
                ->addColumn('discount_info', function ($voucher) {
                    $scope = $voucher->scope ?: 'all';
                    $scopeLabel = match ($scope) {
                        'shipping' => '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle ms-1" style="font-size:.6rem">ONGKIR</span>',
                        'products' => '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle ms-1" style="font-size:.6rem">PRODUK</span>',
                        default    => '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle ms-1" style="font-size:.6rem">SEMUA</span>',
                    };
                    if ($voucher->type === 'percent') {
                        return '<div class="fw-bold text-dark">' . $voucher->value . '% ' . $scopeLabel . '</div>';
                    }
                    return '<div class="fw-bold text-dark">Rp ' . number_format($voucher->value, 0, ',', '.') . ' ' . $scopeLabel . '</div>';
                })
                ->addColumn('validity', function ($voucher) {
                    $start = $voucher->starts_at ? $voucher->starts_at->format('d M Y') : '-';
                    $end = $voucher->expires_at ? $voucher->expires_at->format('d M Y') : '-';
                    return '<div class="small">
                                <div class="text-muted"><i class="bi bi-play-circle me-1"></i> ' . $start . '</div>
                                <div class="text-danger mt-1"><i class="bi bi-stop-circle me-1"></i> ' . $end . '</div>
                            </div>';
                })
                ->addColumn('usage_info', function ($voucher) {
                    $max = $voucher->max_usage ? $voucher->max_usage : '&#8734;';
                    return '<div class="text-muted small">' . $voucher->used_count . ' / ' . $max . '</div>';
                })
                ->addColumn('status_badge', function ($voucher) {
                    if (!$voucher->is_active) {
                        return '<span class="badge badge-status bg-secondary">Nonaktif</span>';
                    }
                    if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                        return '<span class="badge badge-status bg-danger">Kedaluwarsa</span>';
                    }
                    return '<span class="badge badge-status bg-success">Aktif</span>';
                })
                ->addColumn('action', function ($voucher) {
                    $editUrl = route('admin.vouchers.edit', $voucher);
                    $deleteUrl = route('admin.vouchers.destroy', $voucher);
                    $csrf = csrf_token();
                    return '
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="' . $editUrl . '" class="btn btn-sm btn-light text-primary rounded-pill px-2" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form id="delete-' . $voucher->id . '" action="' . $deleteUrl . '" method="POST">
                                <input type="hidden" name="_token" value="' . $csrf . '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" onclick="confirmDelete(\'delete-' . $voucher->id . '\')" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['code_badge', 'discount_info', 'validity', 'usage_info', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.vouchers.index');
    }

    public function create()
    {
        return view('admin.vouchers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code',
            'scope' => 'required|in:all,products,shipping',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'max_usage' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = Str::upper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['used_count'] = 0;

        Voucher::create($validated);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'scope' => 'required|in:all,products,shipping',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'max_usage' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $validated['code'] = Str::upper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $voucher->update($validated);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil dihapus.');
    }
}
