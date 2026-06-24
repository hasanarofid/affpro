<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Page::select('pages.*');

            return DataTables::of($query)
                ->addColumn('title_val', function ($page) {
                    return '<div class="fw-medium">' . $page->title . '</div>';
                })
                ->addColumn('slug_val', function ($page) {
                    return '<div class="text-muted small">/page/' . $page->slug . '</div>' .
                        '<div class="text-info x-small mt-1">' . ($page->category == 'footer_navigasi' ? '<i class="bi bi-tag-fill me-1"></i>Footer Navigasi' : '') . '</div>';
                })
                ->addColumn('status_badge', function ($page) {
                    $status = $page->is_active ? 'Aktif' : 'Draft';
                    $color = $page->is_active ? 'success' : 'secondary';
                    return '<span class="badge badge-status bg-' . $color . '">' . $status . '</span>';
                })
                ->addColumn('created_date', function ($page) {
                    return '<div class="small">' . $page->created_at->format('d M Y') . '</div>';
                })
                ->addColumn('action', function ($page) {
                    $editUrl = route('admin.pages.edit', $page);
                    $viewUrl = route('page.show', $page->slug);
                    $deleteUrl = route('admin.pages.destroy', $page);
                    $csrf = csrf_token();
                    return '
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="' . $viewUrl . '" class="btn btn-sm btn-light text-info rounded-pill px-2" title="Lihat Halaman" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="' . $editUrl . '" class="btn btn-sm btn-light text-primary rounded-pill px-2" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form id="del-p-' . $page->id . '" action="' . $deleteUrl . '" method="POST">
                                <input type="hidden" name="_token" value="' . $csrf . '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" onclick="confirmDelete(\'del-p-' . $page->id . '\')" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['title_val', 'slug_val', 'status_badge', 'created_date', 'action'])
                ->make(true);
        }

        return view('admin.pages.index');
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Page::create($validated);
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dibuat.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $page->update($validated);
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dihapus.');
    }
}
