<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = BlogPost::with('author')->select('blog_posts.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($post) {
                    if ($post->featured_image) {
                        return '<img src="' . asset($post->featured_image) . '" class="rounded-lg shadow-sm" width="60" height="40" style="object-fit:cover; border-radius: 8px;">';
                    }
                    return '<div class="bg-light rounded-lg d-flex align-items-center justify-content-center shadow-sm" style="width:60px;height:40px; border-radius: 8px;"><i class="bi bi-image text-muted"></i></div>';
                })
                ->addColumn('post_title', function ($post) {
                    return '<div class="fw-bold text-dark">' . Str::limit($post->title, 50) . '</div>
                            <div class="text-muted small mt-1"><i class="bi bi-clock me-1"></i> ' . $post->created_at->format('d M Y') . '</div>';
                })
                ->addColumn('views_count', function ($post) {
                    return '<div class="fw-medium text-dark">' . $post->views . ' <span class="text-muted ms-1">kali</span></div>';
                })
                ->addColumn('status_badge', function ($post) {
                    $status = $post->is_published ? 'Diterbitkan' : 'Draft';
                    $color = $post->is_published ? 'success' : 'secondary';
                    return '<span class="badge badge-status bg-' . $color . '">' . $status . '</span>';
                })
                ->addColumn('action', function ($post) {
                    $editUrl = route('admin.blog.edit', $post);
                    $viewUrl = route('blog.show', $post->slug);
                    $deleteUrl = route('admin.blog.destroy', $post);
                    $csrf = csrf_token();
                    return '
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="' . $viewUrl . '" class="btn btn-sm btn-light text-info rounded-pill px-2" title="Lihat Postingan" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="' . $editUrl . '" class="btn btn-sm btn-light text-primary rounded-pill px-2" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form id="delete-' . $post->id . '" action="' . $deleteUrl . '" method="POST">
                                <input type="hidden" name="_token" value="' . $csrf . '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" onclick="confirmDelete(\'delete-' . $post->id . '\')" class="btn btn-sm btn-light text-danger rounded-pill px-2" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['image', 'post_title', 'views_count', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.blog.index');
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['author_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/blog'), $filename);
            $validated['featured_image'] = 'uploads/blog/' . $filename;
        }

        BlogPost::create($validated);
        return redirect()->route('admin.blog.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function edit(BlogPost $blog)
    {
        return view('admin.blog.edit', ['post' => $blog]);
    }

    public function update(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/blog'), $filename);
            $validated['featured_image'] = 'uploads/blog/' . $filename;
        }

        $blog->update($validated);
        return redirect()->route('admin.blog.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Artikel berhasil dihapus.');
    }
}
