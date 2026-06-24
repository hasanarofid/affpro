<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ModuleInstallerService;
use App\Services\DevStoreService;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;

class ModuleController extends Controller
{
    /**
     * Modul inti sistem yang wajib aktif dan tidak bisa dinonaktifkan/dihapus.
     */
    protected array $systemModules = ['RajaOngkir', 'WhatsApp'];

    public function index()
    {
        $modules = collect(Module::all())->map(function ($module) {
            $json = $module->json();
            $name = $json->get('name');
            $isSystem = in_array($name, $this->systemModules);

            // Force enable system module if it's currently disabled
            if ($isSystem && !$module->isEnabled()) {
                $module->enable();
            }

            return [
                'name' => $name,
                'alias' => $json->get('alias'),
                'description' => $json->get('description', '-'),
                'version' => $json->get('version', '1.0.0'),
                'enabled' => $module->isEnabled(),
                'is_system' => $isSystem,
                'path' => $module->getPath(),
            ];
        })->values();

        return view('admin.modules.index', compact('modules'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $module = Module::find($request->name);

        if (!$module) {
            return back()->with('error', 'Modul tidak ditemukan.');
        }

        if (in_array($module->getName(), $this->systemModules)) {
            return back()->with('error', "Modul {$request->name} adalah modul inti sistem yang wajib aktif.");
        }

        if ($module->isEnabled()) {
            $module->disable();
            return back()->with('success', "Modul {$request->name} berhasil dinonaktifkan.");
        } else {
            $module->enable();
            return back()->with('success', "Modul {$request->name} berhasil diaktifkan.");
        }
    }

    public function upload(Request $request, ModuleInstallerService $installer)
    {
        $request->validate([
            'module_zip' => 'required|file|mimes:zip|max:51200',
        ]);

        $result = $installer->installFromZip($request->file('module_zip'));

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
        $request->validate(['name' => 'required|string']);
        $module = Module::find($request->name);

        if (!$module) {
            return back()->with('error', 'Modul tidak ditemukan.');
        }

        if (in_array($module->getName(), $this->systemModules)) {
            return back()->with('error', "Modul {$request->name} adalah modul inti sistem dan tidak bisa dihapus.");
        }

        $module->disable();
        \Illuminate\Support\Facades\File::deleteDirectory($module->getPath());
        return back()->with('success', "Modul {$request->name} berhasil dihapus.");
    }

    /**
     * Marketplace: browse modules from DK-DevStore.
     */
    public function marketplace(Request $request, DevStoreService $devStore)
    {
        $license = $devStore->validateLicense();
        $items = $devStore->getProducts(['type' => 'module', 'search' => $request->search ?? '', 'page' => $request->page ?? 1]);

        return view('admin.modules.marketplace', compact('license', 'items'));
    }

    /**
     * Marketplace: install module from DK-DevStore.
     */
    public function marketplaceInstall(Request $request, DevStoreService $devStore)
    {
        $request->validate([
            'slug' => 'required|string',
            'download_url' => 'required|url',
        ]);

        $result = $devStore->installProduct($request->download_url, 'module');

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
