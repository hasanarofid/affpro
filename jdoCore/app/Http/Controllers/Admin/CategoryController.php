<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = Category::with('parent')->withCount('products')->orderBy('parent_id')->orderBy('sort_order');
            return DataTables::of($categories)
                ->addIndexColumn()
                ->editColumn('name', function ($category) {
                    $prefix = $category->parent_id ? '<i class="bi bi-arrow-return-right text-muted me-2"></i> ' : '';
                    $icon = $category->icon ? '<i class="fas ' . $category->icon . ' me-2 text-primary fa-fw"></i>' : '';
                    return $prefix . $icon . '<span class="fw-medium">' . $category->name . '</span>';
                })
                ->addColumn('parent', function ($category) {
                    return $category->parent ? $category->parent->name : '<span class="badge bg-light text-dark">Root</span>';
                })
                ->addColumn('status', function ($category) {
                    $checked = $category->is_active ? 'checked' : '';
                    return '
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input flex-shrink-0" type="checkbox" onchange="toggleStatus(' . $category->id . ', this)" ' . $checked . ' style="width: 2.5em; height: 1.2em; cursor: pointer;">
                        </div>';
                })
                ->addColumn('action', function ($category) {
                    $parentId = $category->parent_id ? $category->parent_id : "''";
                    $editBtn = '<button type="button" class="btn btn-sm btn-light text-primary rounded-pill px-2" title="Edit" onclick="editCategory(' . $category->id . ', \'' . addslashes($category->name) . '\', ' . $parentId . ', \'' . addslashes($category->icon ?: '') . '\', ' . $category->sort_order . ', ' . ($category->is_active ? 1 : 0) . ')"><i class="bi bi-pencil-square"></i></button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Hapus" onclick="deleteCategory(' . $category->id . ')"><i class="bi bi-trash"></i></button>';
                    return '<div class="d-flex gap-2 justify-content-end">' . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['name', 'parent', 'status', 'action'])
                ->make(true);
        }

        $parentCategories = Category::roots()->get();
        return view('admin.categories.index', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (empty($validated['sort_order']))
            $validated['sort_order'] = 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        Category::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan.']);
        }
        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->has('sort_order') && empty($validated['sort_order'])) {
            $validated['sort_order'] = 0;
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        // Prevent setting itself as parent
        if ($request->filled('parent_id') && $category->id == $request->parent_id) {
            unset($validated['parent_id']);
        }

        $category->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui.']);
        }
        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
        }
        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
