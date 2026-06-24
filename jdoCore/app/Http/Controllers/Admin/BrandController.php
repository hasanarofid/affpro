<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $brands = Brand::withCount('products')->latest();
            return DataTables::of($brands)
                ->addIndexColumn()
                ->addColumn('action', function ($brand) {
                    $editBtn = '<button type="button" class="btn btn-sm btn-light text-primary rounded-pill px-2" title="Edit" onclick="editBrand(' . $brand->id . ', \'' . addslashes($brand->name) . '\', \'' . addslashes($brand->slug) . '\', ' . ($brand->is_active ? 1 : 0) . ')"><i class="bi bi-pencil-square"></i></button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Hapus" onclick="deleteBrand(' . $brand->id . ')"><i class="bi bi-trash"></i></button>';
                    return '<div class="d-flex gap-2 justify-content-end">' . $editBtn . $deleteBtn . '</div>';
                })
                ->editColumn('is_active', function ($brand) {
                    $checked = $brand->is_active ? 'checked' : '';
                    return '<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" ' . $checked . ' onchange="toggleStatus(' . $brand->id . ', this)"></div>';
                })
                ->rawColumns(['action', 'is_active'])
                ->make(true);
        }

        return view('admin.brands.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/brands'), $filename);
            $validated['logo'] = 'uploads/brands/' . $filename;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Brand::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Merek berhasil ditambahkan.']);
        }
        return redirect()->route('admin.brands.index')->with('success', 'Merek berhasil ditambahkan.');
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $brand->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Merek berhasil diperbarui.']);
        }
        return redirect()->route('admin.brands.index')->with('success', 'Merek berhasil diperbarui.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Merek berhasil dihapus.']);
        }
        return redirect()->route('admin.brands.index')->with('success', 'Merek berhasil dihapus.');
    }
}
