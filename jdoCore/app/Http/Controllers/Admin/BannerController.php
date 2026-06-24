<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->paginate(15);
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:500',
            'image' => 'required|image|max:5120',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = 0;
        }

        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/banners'), $filename);
        $validated['image'] = 'uploads/banners/' . $filename;
        $validated['is_active'] = $request->boolean('is_active', true);

        $banner = Banner::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil ditambahkan.',
                'banner' => $banner,
                'image_url' => asset($banner->image),
                'delete_url' => route('admin.banners.destroy', $banner)
            ]);
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:500',
            'image' => 'nullable|image|max:5120',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = 0;
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banners'), $filename);
            $validated['image'] = 'uploads/banners/' . $filename;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $banner->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil diperbarui.'
            ]);
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Banner berhasil dihapus.']);
        }
        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }
}
