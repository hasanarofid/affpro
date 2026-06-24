<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use App\Services\ThemeInstallerService;
use App\Services\DevStoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ThemeController extends Controller
{
    public function index(ThemeService $themeService)
    {
        $themes = $themeService->getAvailableThemes();
        return view('admin.themes.index', compact('themes'));
    }

    public function activate(Request $request, ThemeService $themeService)
    {
        $request->validate(['slug' => 'required|string']);
        $path = resource_path('themes/' . $request->slug);

        if (!File::isDirectory($path)) {
            return back()->with('error', 'Tema tidak ditemukan.');
        }

        $themeService->setActiveTheme($request->slug);
        return back()->with('success', 'Tema berhasil diaktifkan!');
    }

    public function upload(Request $request, ThemeInstallerService $installer)
    {
        $request->validate([
            'theme_zip' => 'required|file|mimes:zip|max:20480',
        ]);

        $result = $installer->installFromZip($request->file('theme_zip'));

        if ($result['success']) {
            $msg = $result['message'];
            if (!empty($result['warnings'])) {
                $msg .= ' ⚠️ ' . implode('; ', $result['warnings']);
            }
            return back()->with('success', $msg);
        }

        return back()->with('error', $result['message']);
    }

    public function delete(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $slug = $request->slug;

        if ($slug === 'default') {
            return back()->with('error', 'Tema default tidak bisa dihapus.');
        }

        $themeService = app(ThemeService::class);
        if ($themeService->getActiveTheme() === $slug) {
            return back()->with('error', 'Tidak bisa menghapus tema yang sedang aktif.');
        }

        $path = resource_path('themes/' . $slug);
        $publicPath = public_path('themes/' . $slug);

        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
            if (File::isDirectory($publicPath)) {
                File::deleteDirectory($publicPath);
            }
            return back()->with('success', 'Tema berhasil dihapus.');
        }

        return back()->with('error', 'Tema tidak ditemukan.');
    }

    /**
     * Marketplace: browse themes from DK-DevStore.
     */
    public function marketplace(Request $request, DevStoreService $devStore)
    {
        $license = $devStore->validateLicense();
        $items = $devStore->getProducts(['type' => 'theme', 'search' => $request->search ?? '', 'page' => $request->page ?? 1]);

        return view('admin.themes.marketplace', compact('license', 'items'));
    }

    /**
     * Marketplace: install theme from DK-DevStore.
     */
    public function marketplaceInstall(Request $request, DevStoreService $devStore)
    {
        $request->validate([
            'slug' => 'required|string',
            'download_url' => 'required|url',
        ]);

        $result = $devStore->installProduct($request->download_url, 'theme');

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
