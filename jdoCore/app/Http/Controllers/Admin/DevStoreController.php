<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DevStoreService;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\File;

class DevStoreController extends Controller
{
    /**
     * Get all locally installed module/theme slugs with their versions.
     * Returns: ['slugname' => 'version', ...]
     */
    private function getInstalledSlugs(): array
    {
        $installed = [];

        // Modules
        foreach (Module::all() as $module) {
            $json = $module->json();
            $slug = strtolower($json->get('alias', $json->get('name')));
            $version = $json->get('version', '1.0.0');
            $installed[$slug] = $version;
        }

        // Themes
        $themesPath = resource_path('themes');
        if (File::isDirectory($themesPath)) {
            foreach (File::directories($themesPath) as $themeDir) {
                $configFile = $themeDir . '/config.json';
                if (File::exists($configFile)) {
                    $config = json_decode(File::get($configFile), true);
                    if ($config) {
                        $slug = strtolower($config['alias'] ?? $config['name'] ?? basename($themeDir));
                        $installed[$slug] = $config['version'] ?? '1.0.0';
                    }
                }
            }
        }

        return $installed;
    }

    /**
     * Enrich product list with local installation status.
     */
    private function enrichProducts(array $products, array $installedSlugs): array
    {
        return array_map(function ($product) use ($installedSlugs) {
            $slug = strtolower($product['slug'] ?? '');

            $product['is_installed'] = isset($installedSlugs[$slug]);
            $product['installed_version'] = $installedSlugs[$slug] ?? null;

            // If installed locally, also consider it as "owned" for UX purposes
            if ($product['is_installed'] && !($product['is_owned'] ?? false)) {
                $product['is_installed_only'] = true; // installed but not officially owned
            }

            return $product;
        }, $products);
    }

    /**
     * Main DK-DevStore page — browse products (themes & modules).
     */
    public function index(Request $request, DevStoreService $devStore)
    {
        $configured = $devStore->isConfigured();
        $license = null;
        $categories = [];
        $products = [];
        $pagination = null;
        $myProducts = [];

        if ($configured) {
            // Validate license
            $license = $devStore->validateLicense();

            // Get categories
            $catResponse = $devStore->getCategories();
            $categories = $catResponse['categories'] ?? [];

            // Build filters
            $filters = [];
            if ($request->filled('type')) {
                $filters['type'] = $request->type;
            }
            if ($request->filled('category')) {
                $filters['category'] = $request->category;
            }
            if ($request->filled('search')) {
                $filters['search'] = $request->search;
            }
            $filters['sort'] = $request->input('sort', 'newest');
            $filters['per_page'] = 12;
            $filters['page'] = $request->input('page', 1);

            // Get products from API
            $prodResponse = $devStore->getProducts($filters);
            $products = $prodResponse['products'] ?? [];
            $pagination = $prodResponse['pagination'] ?? null;

            // Enrich with local installation status
            $installedSlugs = $this->getInstalledSlugs();
            $products = $this->enrichProducts($products, $installedSlugs);
        }

        return view('admin.devstore.index', compact(
            'configured',
            'license',
            'categories',
            'products',
            'pagination'
        ));
    }

    /**
     * Product detail page.
     */
    public function show(string $slug, DevStoreService $devStore)
    {
        if (!$devStore->isConfigured()) {
            return redirect()->route('admin.devstore.index')
                ->with('error', 'License Key belum dikonfigurasi.');
        }

        $response = $devStore->getProductDetail($slug);

        if (isset($response['error'])) {
            return redirect()->route('admin.devstore.index')
                ->with('error', $response['error']);
        }

        $product = $response['product'] ?? null;
        $isOwned = $response['is_owned'] ?? false;
        $license = $response['license'] ?? null;

        if (!$product) {
            return redirect()->route('admin.devstore.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        // Check local installation
        $installedSlugs = $this->getInstalledSlugs();
        $productSlug = strtolower($product['slug'] ?? $slug);
        $isInstalled = isset($installedSlugs[$productSlug]);
        $installedVersion = $installedSlugs[$productSlug] ?? null;

        return view('admin.devstore.show', compact('product', 'isOwned', 'license', 'isInstalled', 'installedVersion'));
    }

    /**
     * My Products page — list owned themes & modules.
     */
    public function myProducts(DevStoreService $devStore)
    {
        if (!$devStore->isConfigured()) {
            return redirect()->route('admin.devstore.index')
                ->with('error', 'License Key belum dikonfigurasi.');
        }

        $response = $devStore->getMyProducts();
        $products = $response['products'] ?? [];

        // Enrich with local installation status
        $installedSlugs = $this->getInstalledSlugs();
        $products = $this->enrichProducts($products, $installedSlugs);

        return view('admin.devstore.my-products', compact('products'));
    }

    /**
     * Install a product (download ZIP and install).
     */
    public function install(Request $request, DevStoreService $devStore)
    {
        $request->validate([
            'download_url' => 'required|url',
            'type' => 'required|in:theme,module',
        ]);

        $result = $devStore->installProduct($request->download_url, $request->type);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Initialize a purchase (AJAX).
     */
    public function purchaseInit(Request $request, DevStoreService $devStore)
    {
        $request->validate([
            'product_slug' => 'required|string',
            'tier_id' => 'required|integer',
        ]);

        $response = $devStore->initPurchase($request->product_slug, $request->tier_id);

        return response()->json($response);
    }

    /**
     * Create order (AJAX).
     */
    public function purchaseCreate(Request $request, DevStoreService $devStore)
    {
        $request->validate([
            'product_slug' => 'required|string',
            'tier_id' => 'required|integer',
            'payment_method' => 'required|in:manual_transfer,qris',
            'bank_name' => 'nullable|string',
        ]);

        $response = $devStore->createOrder(
            $request->product_slug,
            $request->tier_id,
            $request->payment_method,
            $request->bank_name
        );

        return response()->json($response);
    }

    /**
     * Upload payment proof (AJAX).
     */
    public function purchaseUploadProof(Request $request, DevStoreService $devStore)
    {
        $request->validate([
            'order_number' => 'required|string',
            'proof_image' => 'required|image|max:5120',
        ]);

        $tempPath = $request->file('proof_image')->getPathname();
        $response = $devStore->uploadPaymentProof($request->order_number, $tempPath);

        return response()->json($response);
    }

    /**
     * Check order status (AJAX).
     */
    public function purchaseStatus(Request $request, DevStoreService $devStore)
    {
        $request->validate(['order_number' => 'required|string']);

        $response = $devStore->getOrderStatus($request->order_number);

        return response()->json($response);
    }

    /**
     * Check for updates (AJAX) — scans installed modules/themes vs DK-DevStore.
     */
    public function checkUpdates(DevStoreService $devStore)
    {
        if (!$devStore->isConfigured()) {
            return response()->json(['updates' => [], 'message' => 'Not configured']);
        }

        $items = [];

        // Collect installed modules
        foreach (Module::all() as $module) {
            $json = $module->json();
            $items[] = [
                'product_slug' => strtolower($json->get('alias', $json->get('name'))),
                'current_version' => $json->get('version', '1.0.0'),
            ];
        }

        // Collect installed themes
        $themesPath = resource_path('themes');
        if (File::isDirectory($themesPath)) {
            foreach (File::directories($themesPath) as $themeDir) {
                $configFile = $themeDir . '/config.json';
                if (File::exists($configFile)) {
                    $config = json_decode(File::get($configFile), true);
                    if ($config) {
                        $items[] = [
                            'product_slug' => basename($themeDir),
                            'current_version' => $config['version'] ?? '1.0.0',
                        ];
                    }
                }
            }
        }

        if (empty($items)) {
            return response()->json(['updates' => [], 'message' => 'No installed items']);
        }

        $response = $devStore->checkUpdates($items);

        return response()->json($response);
    }
}
