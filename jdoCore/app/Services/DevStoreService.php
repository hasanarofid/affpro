<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class DevStoreService
{
    protected string $baseUrl;
    protected string $licenseKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            Setting::getValue('devstore_url', config('services.devstore.url', 'https://devstore.dapurkode.com')),
            '/'
        ) . '/api';
        $this->licenseKey = Setting::getValue('devstore_license_key', config('services.devstore.license_key', ''));
    }

    /**
     * Check if DK-DevStore is configured with a license key.
     */
    public function isConfigured(): bool
    {
        return !empty($this->licenseKey);
    }

    /**
     * Get the license key.
     */
    public function getLicenseKey(): string
    {
        return $this->licenseKey;
    }

    // ═══════════════════════════════════════════════════════════════
    //  LICENSE MANAGEMENT (No Bearer token needed)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Validate license and register domain.
     */
    public function validateLicense(?string $domain = null): array
    {
        $domain = $domain ?? request()->getHost();

        $response = $this->post('/license/validate', [
            'license_key' => $this->licenseKey,
            'domain' => $domain,
            'platform' => 'Jadiorder',
        ], withBearer: false);

        // Local Platform Validation Enforcer
        if (isset($response['valid']) && $response['valid']) {
            $productName = strtolower($response['data']['platform'] ?? $response['data']['product'] ?? '');
            
            // If the marketplace returned valid, but the product/platform is not for 'jadiorder', we reject it locally.
            if ($productName && strpos($productName, 'jadiorder') === false) {
                return [
                    'valid' => false,
                    'message' => 'Lisensi ditolak. License Key ini tidak diperuntukkan bagi platform Jadiorder.'
                ];
            }
        }

        return $response;
    }

    /**
     * Get available versions for a license key.
     */
    public function getVersions(?string $licenseKey = null): array
    {
        $key = $licenseKey ?? $this->licenseKey;
        return $this->get("/license/{$key}/updates", withBearer: false);
    }

    /**
     * Get registered domains for a license key.
     */
    public function getDomains(?string $licenseKey = null): array
    {
        $key = $licenseKey ?? $this->licenseKey;
        return $this->get("/license/{$key}/domains", withBearer: false);
    }

    /**
     * Download a specific version file (returns temp file path or null).
     */
    public function downloadVersion(string $licenseKey, int $versionId): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(120)
                ->get("{$this->baseUrl}/license/{$licenseKey}/download/{$versionId}");

            if ($response->successful()) {
                $tmpPath = storage_path('app/tmp/devstore-download-' . time() . '.zip');
                @mkdir(dirname($tmpPath), 0755, true);
                file_put_contents($tmpPath, $response->body());
                return $tmpPath;
            }

            Log::error('DevStore download failed', ['status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error("DevStore download error: {$e->getMessage()}");
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  STORE CATALOG (Bearer token required)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get store categories (theme/module).
     */
    public function getCategories(): array
    {
        return $this->get('/store/categories');
    }

    /**
     * Browse products with optional filters.
     */
    public function getProducts(array $filters = []): array
    {
        return $this->get('/store/products', $filters);
    }

    /**
     * Get product detail by slug.
     */
    public function getProductDetail(string $slug): array
    {
        return $this->get("/store/products/{$slug}");
    }

    /**
     * Get user's owned products.
     */
    public function getMyProducts(): array
    {
        return $this->get('/store/my-products');
    }

    // ═══════════════════════════════════════════════════════════════
    //  OTA UPDATES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Check updates for installed items (bulk).
     *
     * @param array $installedItems [['product_slug' => '...', 'current_version' => '...'], ...]
     */
    public function checkUpdates(array $installedItems): array
    {
        return $this->post('/store/check-updates', ['items' => $installedItems]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  IN-APP PURCHASE
    // ═══════════════════════════════════════════════════════════════

    /**
     * Initialize a purchase (get pricing + payment methods).
     */
    public function initPurchase(string $productSlug, int $tierId): array
    {
        return $this->post('/store/purchase/init', [
            'product_slug' => $productSlug,
            'tier_id' => $tierId,
        ]);
    }

    /**
     * Create an order.
     */
    public function createOrder(string $productSlug, int $tierId, string $paymentMethod, ?string $bankName = null): array
    {
        return $this->post('/store/purchase/create', [
            'product_slug' => $productSlug,
            'tier_id' => $tierId,
            'payment_method' => $paymentMethod,
            'bank_name' => $bankName,
        ]);
    }

    /**
     * Upload payment proof for an order.
     */
    public function uploadPaymentProof(string $orderNumber, string $imagePath): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withToken($this->licenseKey)
                ->attach('proof_image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->baseUrl}/store/purchase/upload-proof", [
                    'order_number' => $orderNumber,
                ]);

            return $response->json() ?? ['error' => 'Empty response'];
        } catch (\Exception $e) {
            Log::error("DevStore upload proof error: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Check order status.
     */
    public function getOrderStatus(string $orderNumber): array
    {
        return $this->get('/store/purchase/status', ['order_number' => $orderNumber]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  INSTALL HELPERS (Download + Install theme/module from store)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Download and install a product from the DK-DevStore.
     * For owned products only — uses the download_url from the API.
     */
    public function installProduct(string $downloadUrl, string $type): array
    {
        try {
            // 1) Download the ZIP
            $response = Http::withoutVerifying()
                ->timeout(120)
                ->get($downloadUrl);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Gagal mengunduh file dari DK-DevStore.'];
            }

            // 2) Save to temp file
            $tempFile = storage_path('app/tmp/devstore-install-' . time() . '.zip');
            @mkdir(dirname($tempFile), 0755, true);
            file_put_contents($tempFile, $response->body());

            // 3) Create UploadedFile instance
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile,
                basename($tempFile),
                'application/zip',
                null,
                true
            );

            // 4) Install using appropriate installer
            if ($type === 'theme') {
                $installer = app(ThemeInstallerService::class);
            } else {
                $installer = app(ModuleInstallerService::class);
            }

            $result = $installer->installFromZip($uploadedFile);

            // 5) Cleanup
            @unlink($tempFile);

            // 6) Log
            if ($result['success']) {
                Log::info("DK-DevStore: {$type} installed successfully via download");
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("DK-DevStore install error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error saat menginstal: ' . $e->getMessage()];
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  HTTP HELPERS
    // ═══════════════════════════════════════════════════════════════

    protected function get(string $endpoint, array $query = [], bool $withBearer = true): array
    {
        try {
            $http = Http::withoutVerifying()->timeout(15);

            if ($withBearer) {
                $http = $http->withToken($this->licenseKey);
            }

            $response = $http->get("{$this->baseUrl}{$endpoint}", $query);

            return $response->json() ?? ['error' => 'Empty response'];
        } catch (\Exception $e) {
            Log::error("DevStore API error: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }

    protected function post(string $endpoint, array $data = [], bool $withBearer = true): array
    {
        try {
            $http = Http::withoutVerifying()->timeout(15);

            if ($withBearer) {
                $http = $http->withToken($this->licenseKey);
            }

            $response = $http->post("{$this->baseUrl}{$endpoint}", $data);

            return $response->json() ?? ['error' => 'Empty response'];
        } catch (\Exception $e) {
            Log::error("DevStore API error: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }
}
