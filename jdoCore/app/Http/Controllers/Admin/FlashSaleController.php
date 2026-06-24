<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\FlashSaleProduct;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FlashSaleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = FlashSale::withCount('products');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title_format', function ($flashSale) {
                    return '<span class="fw-medium text-dark">' . $flashSale->title . '</span>';
                })
                ->addColumn('start_time_format', function ($flashSale) {
                    return '<i class="far fa-calendar text-muted me-1"></i> ' . $flashSale->start_time->translatedFormat('d M Y, H:i');
                })
                ->addColumn('end_time_format', function ($flashSale) {
                    return '<i class="far fa-calendar-check text-muted me-1"></i> ' . $flashSale->end_time->translatedFormat('d M Y, H:i');
                })
                ->addColumn('products_count_format', function ($flashSale) {
                    return '<div class="text-center"><span class="badge bg-light text-primary border" style="font-size: 0.75rem;">' . $flashSale->products_count . ' Produk</span></div>';
                })
                ->addColumn('status_badge', function ($flashSale) {
                    $now = now();
                    $isOngoing = $flashSale->is_active && $now->between($flashSale->start_time, $flashSale->end_time);
                    $isUpcoming = $flashSale->is_active && $now->lt($flashSale->start_time);

                    if (!$flashSale->is_active) {
                        return '<div class="text-center"><span class="badge badge-status bg-secondary">Tidak Aktif</span></div>';
                    } elseif ($isOngoing) {
                        return '<div class="text-center"><span class="badge badge-status bg-success"><i class="fas fa-bolt me-1"></i> Berlangsung</span></div>';
                    } elseif ($isUpcoming) {
                        return '<div class="text-center"><span class="badge badge-status bg-info text-primary"><i class="fas fa-clock me-1"></i> Akan Datang</span></div>';
                    } else {
                        return '<div class="text-center"><span class="badge badge-status bg-danger"><i class="fas fa-flag-checkered me-1"></i> Berakhir</span></div>';
                    }
                })
                ->addColumn('action', function ($flashSale) {
                    $showUrl = route('admin.flash-sales.show', $flashSale);
                    $editUrl = route('admin.flash-sales.edit', $flashSale);
                    $deleteUrl = route('admin.flash-sales.destroy', $flashSale);
                    $csrf = csrf_token();
                    return '
                        <div class="d-flex justify-content-end gap-2">
                            <a href="' . $showUrl . '" title="Kelola Produk" class="btn btn-sm btn-light text-info rounded-pill px-2">
                                <i class="bi bi-box-seam"></i>
                            </a>
                            <a href="' . $editUrl . '" title="Edit Event" class="btn btn-sm btn-light text-primary rounded-pill px-2">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="' . $deleteUrl . '" method="POST" id="delete-fs-' . $flashSale->id . '">
                                <input type="hidden" name="_token" value="' . $csrf . '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Hapus Event" onclick="confirmDelete(\'delete-fs-' . $flashSale->id . '\')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['title_format', 'start_time_format', 'end_time_format', 'products_count_format', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.flash_sales.index');
    }

    public function create()
    {
        return view('admin.flash_sales.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        FlashSale::create($validated);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Flash Sale berhasil ditambahkan.');
    }

    public function show(FlashSale $flashSale)
    {
        $flashSale->load('products.product');
        // Exclude products that are already in this flash sale
        $existingProductIds = $flashSale->products->pluck('product_id')->toArray();
        $products = Product::active()->whereNotIn('id', $existingProductIds)->get();

        return view('admin.flash_sales.show', compact('flashSale', 'products'));
    }

    public function edit(FlashSale $flashSale)
    {
        return view('admin.flash_sales.edit', compact('flashSale'));
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $flashSale->update($validated);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Flash Sale berhasil diperbarui.');
    }

    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();
        return redirect()->route('admin.flash-sales.index')->with('success', 'Flash Sale berhasil dihapus.');
    }

    public function addProduct(Request $request, FlashSale $flash_sale)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'discount_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
        ]);

        // check if product exists in this flash sale
        if ($flash_sale->products()->where('product_id', $validated['product_id'])->exists()) {
            return back()->with('error', 'Produk sudah ada di Flash Sale ini.');
        }

        $flash_sale->products()->create($validated);

        return back()->with('success', 'Produk berhasil ditambahkan ke Flash Sale.');
    }

    public function removeProduct(FlashSale $flash_sale, FlashSaleProduct $product)
    {
        if ($product->flash_sale_id !== $flash_sale->id) {
            abort(404);
        }

        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus dari Flash Sale.');
    }
}
