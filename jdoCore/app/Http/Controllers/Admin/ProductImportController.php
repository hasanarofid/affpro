<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantType;
use App\Models\ProductVariantValue;
use App\Models\Category;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductImportController extends Controller
{
    public function create()
    {
        return view('admin.products.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'category_id' => 'required|exists:categories,id'
        ]);

        try {
            DB::beginTransaction();

            $collection = (new FastExcel)->import($request->file('file'));

            foreach ($collection as $row) {
                $name = $row['Nama Produk'] ?? null;
                if (empty($name)) {
                    continue;
                }

                $description = $row['Deskripsi Panjang'] ?? ($row['Deskripsi Pendek'] ?? '');
                
                // Handle different price columns from different BigSeller export templates
                $price = floatval($row['Harga Promo'] ?? 0);
                if ($price <= 0) {
                    $price = floatval($row['Diskon'] ?? 0);
                }
                if ($price <= 0) {
                    $price = floatval($row['Harga'] ?? 0);
                }
                
                $stock = intval($row['Stok'] ?? 0);
                $weight = intval($row['Berat Paket'] ?? 0);
                $sku = $row['SKU'] ?? null;

                // Check if product exists
                $product = Product::where('name', $name)->first();

                // Determine if it has variants based on either old or new format
                $hasVariants = (!empty($row['Nama Variasi 1']) || !empty($row['Variasi'])) ? 1 : 0;

                if (!$product) {
                    $product = Product::create([
                        'name' => $name,
                        'slug' => Str::slug($name) . '-' . uniqid(),
                        'category_id' => $request->category_id,
                        'description' => $description,
                        'base_price' => $price,
                        'stock' => $stock,
                        'weight' => $weight,
                        'sku' => $sku,
                        'has_variants' => $hasVariants,
                        'is_active' => 1
                    ]);
                } else {
                    $product->update([
                        // If it's a new row for the same product, we might want to update price or just accumulate stock
                        'stock' => $product->stock + $stock, // accumulate stock if multiple rows for variants
                        'has_variants' => ($hasVariants || $product->has_variants) ? 1 : 0,
                    ]);
                    
                    // Only update base price if the current base price is 0 or we are processing the first row
                    if ($product->base_price <= 0 && $price > 0) {
                        $product->update(['base_price' => $price]);
                    }
                }

                // Handle Images (Foto Produk 1 to 9) - if present in the template
                for ($i = 1; $i <= 9; $i++) {
                    $imgUrl = $row["Foto Produk $i"] ?? null;
                    if (!empty($imgUrl) && is_string($imgUrl) && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                        ProductImage::firstOrCreate([
                            'product_id' => $product->id,
                            'path' => $imgUrl
                        ], [
                            'is_primary' => ($i == 1 && $product->images()->count() == 0) ? 1 : 0
                        ]);
                    }
                }

                $variantValueIds = [];
                $variantNameCombined = '';

                // Handle Variants for Old Template (Nama Variasi 1, Opsi Variasi 1)
                if (!empty($row['Nama Variasi 1']) && !empty($row['Opsi Variasi 1'])) {
                    $variantNames = [];
                    $variantValues = [];

                    for ($v = 1; $v <= 3; $v++) {
                        $vName = $row["Nama Variasi $v"] ?? null;
                        $vOpt = $row["Opsi Variasi $v"] ?? null;

                        if (!empty($vName) && !empty($vOpt)) {
                            $type = ProductVariantType::firstOrCreate([
                                'product_id' => $product->id,
                                'name' => $vName
                            ]);

                            $value = ProductVariantValue::firstOrCreate([
                                'variant_type_id' => $type->id,
                                'value' => $vOpt
                            ]);
                            
                            $variantNames[] = $vName;
                            $variantValues[] = $vOpt;
                            $variantValueIds[] = $value->id;
                        }
                    }
                    if (count($variantValues) > 0) {
                        $variantNameCombined = implode(' - ', $variantValues);
                    }
                } 
                // Handle Variants for New Template (Variasi)
                elseif (!empty($row['Variasi'])) {
                    $vOpt = $row['Variasi'];
                    
                    // Since new template doesn't specify variant name (like "Warna" or "Ukuran"), we use a generic name
                    $type = ProductVariantType::firstOrCreate([
                        'product_id' => $product->id,
                        'name' => 'Variasi'
                    ]);

                    $value = ProductVariantValue::firstOrCreate([
                        'variant_type_id' => $type->id,
                        'value' => $vOpt
                    ]);

                    $variantValueIds[] = $value->id;
                    $variantNameCombined = $vOpt;
                }

                // Create the physical variant row if we have values
                if (!empty($variantValueIds)) {
                    $baseSku = !empty($sku) ? $sku : (!empty($product->sku) ? $product->sku : null);
                    $variantSku = $baseSku ? $baseSku . '-' . Str::slug($variantNameCombined) : uniqid('VAR-');
                    
                    $variant = ProductVariant::firstOrCreate([
                        'product_id' => $product->id,
                        'sku' => $variantSku,
                    ], [
                        'price' => $price,
                        'stock' => $stock,
                    ]);
                    
                    // Attach to combination table (many to many)
                    $variant->values()->syncWithoutDetaching($variantValueIds);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diimpor dari BigSeller.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Excel Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
}
