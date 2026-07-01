<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with(['category', 'primaryImage'])
                ->select('products.*')
                ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
                ->when($request->status !== null, fn($q) => $q->where('is_active', $request->status));

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($product) {
                    if ($product->primaryImage) {
                        return '<img src="' . asset($product->primaryImage->path) . '" class="rounded-lg shadow-sm" width="48" height="48" style="object-fit:cover; border-radius: 10px;">';
                    }
                    return '<div class="bg-light rounded-lg d-flex align-items-center justify-content-center shadow-sm" style="width:48px;height:48px; border-radius: 10px;"><i class="bi bi-image text-muted"></i></div>';
                })
                ->addColumn('name_info', function ($product) {
                    $html = '<div class="fw-bold text-dark">' . Str::limit($product->name, 40) . '</div><div class="d-flex gap-1 mt-1">';
                    if ($product->type === 'digital') {
                        $html .= '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size:.6rem">DIGITAL</span>';
                    }
                    if ($product->has_variants) {
                        $html .= '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" style="font-size:.6rem">VARIAN</span>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('category_name', function ($product) {
                    return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.7rem;">' . ($product->category->name ?? '-') . '</span>';
                })
                ->editColumn('base_price', function ($product) {
                    if ($product->discount_price) {
                        return '<div class="text-muted text-decoration-line-through x-small" style="font-size:0.7rem">Rp ' . number_format($product->base_price, 0, ',', '.') . '</div><div class="fw-bold text-primary">Rp ' . number_format($product->discount_price, 0, ',', '.') . '</div>';
                    }
                    return '<div class="fw-bold text-dark">Rp ' . number_format($product->base_price, 0, ',', '.') . '</div>';
                })
                ->editColumn('stock', function ($product) {
                    $stock = $product->effective_stock;
                    if ($product->has_variants) {
                        return '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" title="Total stok dari semua varian">' . $stock . '</span>';
                    } elseif ($stock <= $product->min_stock_alert && $stock > 0) {
                        return '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">' . $stock . '</span>';
                    } elseif ($stock === 0) {
                        return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Habis</span>';
                    }
                    return '<span class="fw-semibold text-dark">' . $stock . '</span>';
                })
                ->editColumn('is_active', function ($product) {
                    $status = $product->is_active ? 'Aktif' : 'Draft';
                    $color = $product->is_active ? 'success' : 'secondary';
                    return '<span class="badge badge-status bg-' . $color . '">' . $status . '</span>';
                })
                ->addColumn('action', function ($product) {
                    $editUrl = route('admin.products.edit', $product);
                    $viewUrl = route('products.show', $product->slug);
                    $deleteUrl = route('admin.products.destroy', $product);
                    $csrf = csrf_token();
                    return '
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="' . $viewUrl . '" class="btn btn-sm btn-light text-info rounded-pill px-2" title="Lihat Produk" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="' . $editUrl . '" class="btn btn-sm btn-light text-primary rounded-pill px-2" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form id="delete-' . $product->id . '" action="' . $deleteUrl . '" method="POST">
                                <input type="hidden" name="_token" value="' . $csrf . '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" onclick="confirmDelete(\'delete-' . $product->id . '\')" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['image', 'name_info', 'category_name', 'base_price', 'stock', 'is_active', 'action'])
                ->make(true);
        }

        $categories = Category::active()->get();
        return view('admin.products.index', compact('categories'));
    }

    public function generateDescription(Request $request, \App\Services\GeminiService $gemini)
    {
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        if (!$gemini->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Gemini API Key belum dikonfigurasi. Silakan atur di menu Pengaturan.'], 400);
        }

        $category = Category::find($request->category_id);
        $description = $gemini->generateProductDescription($request->name, $category->name);

        if ($description) {
            return response()->json(['success' => true, 'description' => $description]);
        }

        $errorMessage = $gemini->getLastError() ?: 'Gagal menghasilkan deskripsi dari AI. Silakan coba lagi.';
        return response()->json(['success' => false, 'message' => $errorMessage], 500);
    }

    public function create()
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:64|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|integer|min:0',
            'stock' => 'required|integer|min:0',
            'min_order' => 'required|integer|min:1',
            'min_stock_alert' => 'integer|min:0',
            'type' => 'required|in:physical,digital',
            'digital_info_text' => 'nullable|string',
            'digital_file' => 'nullable|file|max:10240',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        $count = Product::where('slug', $validated['slug'])->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        if ($request->hasFile('digital_file')) {
            $file = $request->file('digital_file');
            $filename = time() . '_digital_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/digital_files'), $filename);
            $validated['digital_file_path'] = 'uploads/digital_files/' . $filename;
        }

        DB::transaction(function () use ($request, $validated, &$product) {
            $hasVariants = !empty($request->input('variant_types'));
            $validated['has_variants'] = $hasVariants;

            $product = Product::create($validated);

            // Handle images
            $this->handleImages($request, $product);

            // Handle variants
            if ($hasVariants) {
                $this->saveVariants($request, $product);
            }

            // Handle wholesale prices
            $this->saveWholesale($request, $product);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'variantTypes.values', 'variants.values', 'wholesalePrices']);
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => ['nullable', 'string', 'max:64', \Illuminate\Validation\Rule::unique('products', 'sku')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|integer|min:0',
            'stock' => 'required|integer|min:0',
            'min_order' => 'required|integer|min:1',
            'min_stock_alert' => 'integer|min:0',
            'type' => 'required|in:physical,digital',
            'digital_info_text' => 'nullable|string',
            'digital_file' => 'nullable|file|max:10240',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        if ($request->hasFile('digital_file')) {
            $file = $request->file('digital_file');
            $filename = time() . '_digital_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/digital_files'), $filename);
            $validated['digital_file_path'] = 'uploads/digital_files/' . $filename;
        } elseif ($request->boolean('remove_digital_file')) {
            $validated['digital_file_path'] = null;
        }

        DB::transaction(function () use ($request, $validated, $product) {
            $hasVariants = !empty($request->input('variant_types'));
            $validated['has_variants'] = $hasVariants;

            $product->update($validated);

            // Handle images
            $this->handleImages($request, $product);

            // Handle variants
            if ($hasVariants) {
                $this->saveVariants($request, $product);
            } else {
                // Remove all variants if none submitted
                $product->variants()->each(function (\App\Models\ProductVariant $v) {
                    $v->values()->detach();
                    $v->delete();
                });
                $product->variantTypes()->each(function (\App\Models\ProductVariantType $t) {
                    $t->values()->delete();
                    $t->delete();
                });
            }

            // Handle wholesale prices
            $this->saveWholesale($request, $product);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // ------- Private Helpers -------

    private function handleImages(Request $request, Product $product): void
    {
        if (!$request->hasFile('images'))
            return;

        $maxSort = $product->images()->max('sort_order') ?? -1;
        $isFirst = $product->images()->count() === 0;

        foreach ($request->file('images') as $file) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);

            $product->images()->create([
                'path' => 'uploads/products/' . $filename,
                'sort_order' => ++$maxSort,
                'is_primary' => $isFirst,
            ]);
            $isFirst = false;
        }
    }

    private function saveVariants(Request $request, Product $product): void
    {
        $typeData = $request->input('variant_types', []);
        $existingTypeIds = $product->variantTypes()->pluck('id')->toArray();
        $keptTypeIds = [];

        // Save variant types & values
        $typeMap = []; // ti => type model
        foreach ($typeData as $ti => $td) {
            if (empty($td['name']))
                continue;

            if (!empty($td['id']) && in_array($td['id'], $existingTypeIds)) {
                $type = $product->variantTypes()->find($td['id']);
                $type->update(['name' => $td['name'], 'sort_order' => $ti]);
            } else {
                $type = $product->variantTypes()->create(['name' => $td['name'], 'sort_order' => $ti]);
            }
            $keptTypeIds[] = $type->id;
            $typeMap[$ti] = $type;

            // Save values
            $existingValueIds = $type->values()->pluck('id')->toArray();
            $keptValueIds = [];
            foreach (($td['values'] ?? []) as $vi => $vd) {
                if (empty($vd['value']))
                    continue;
                if (!empty($vd['id']) && in_array($vd['id'], $existingValueIds)) {
                    $val = $type->values()->find($vd['id']);
                    $val->update(['value' => $vd['value'], 'sort_order' => $vi]);
                } else {
                    $val = $type->values()->create(['value' => $vd['value'], 'sort_order' => $vi]);
                }
                $keptValueIds[] = $val->id;
            }
            // Delete removed values
            $type->values()->whereNotIn('id', $keptValueIds)->delete();
        }

        // Delete removed types
        $product->variantTypes()->whereNotIn('id', $keptTypeIds)->get()->each(function (\App\Models\ProductVariantType $t) {
            $t->values()->delete();
            $t->delete();
        });

        // Save variant combinations
        $variantData = $request->input('variants', []);
        $existingVariantIds = $product->variants()->pluck('id')->toArray();
        $keptVariantIds = [];

        foreach ($variantData as $ci => $vd) {
            $data = [
                'sku' => $vd['sku'] ?? null,
                'price' => $vd['price'] ?? $product->base_price,
                'stock' => $vd['stock'] ?? 0,
                'weight' => $product->weight,
                'is_active' => true,
            ];

            if ($request->hasFile("variant_images.{$ci}")) {
                $file = $request->file("variant_images.{$ci}");
                $filename = time() . '_variant_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/products/variants'), $filename);
                $data['image_path'] = 'uploads/products/variants/' . $filename;
            }

            if (!empty($vd['id']) && in_array($vd['id'], $existingVariantIds)) {
                $variant = $product->variants()->find($vd['id']);
                $variant->update($data);
            } else {
                $data['product_id'] = $product->id;
                $variant = $product->variants()->create($data);
            }
            $keptVariantIds[] = $variant->id;

            // Sync combination values
            $values = json_decode($vd['values'] ?? '[]', true);
            $valueIds = [];
            foreach ($values as $valueName) {
                $pvv = \App\Models\ProductVariantValue::whereHas('variantType', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })->where('value', $valueName)->first();
                if ($pvv)
                    $valueIds[] = $pvv->id;
            }
            $variant->values()->sync($valueIds);
        }

        // Delete removed variants
        $product->variants()->whereNotIn('id', $keptVariantIds)->get()->each(function (\App\Models\ProductVariant $v) {
            $v->values()->detach();
            $v->delete();
        });
    }

    private function saveWholesale(Request $request, Product $product): void
    {
        $wholesaleData = $request->input('wholesale', []);
        $existingIds = $product->wholesalePrices()->pluck('id')->toArray();
        $keptIds = [];

        foreach ($wholesaleData as $wd) {
            if (empty($wd['min_qty']) || empty($wd['price']))
                continue;

            $data = ['min_qty' => $wd['min_qty'], 'price' => $wd['price']];

            if (!empty($wd['id']) && in_array($wd['id'], $existingIds)) {
                $wp = $product->wholesalePrices()->find($wd['id']);
                $wp->update($data);
                $keptIds[] = $wp->id;
            } else {
                $wp = $product->wholesalePrices()->create($data);
                $keptIds[] = $wp->id;
            }
        }

        // Delete removed tiers
        $product->wholesalePrices()->whereNotIn('id', $keptIds)->delete();
    }
}
