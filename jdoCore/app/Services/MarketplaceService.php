<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class MarketplaceService
{
    protected string $baseUrl;
    protected ?string $licenseKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            Setting::getValue('marketplace_url', 'https://marketplace.jadiorder.com/api/v1'),
            '/'
        );
        $this->licenseKey = Setting::getValue('marketplace_license_key');
    }

    /**
     * Check if marketplace is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->licenseKey);
    }

    /**
     * Verify license key with marketplace.
     */
    public function verifyLicense(): array
    {
        if (!$this->isConfigured()) {
            return ['valid' => false, 'message' => 'License key belum dikonfigurasi.'];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->authHeaders())
                ->get("{$this->baseUrl}/license/verify");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'valid' => $data['valid'] ?? false,
                    'message' => $data['message'] ?? 'OK',
                    'plan' => $data['plan'] ?? 'free',
                    'expires_at' => $data['expires_at'] ?? null,
                    'domain' => $data['domain'] ?? null,
                ];
            }

            return ['valid' => false, 'message' => 'Gagal memverifikasi license: ' . $response->status()];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Tidak dapat terhubung ke marketplace: ' . $e->getMessage()];
        }
    }

    /**
     * Browse available items from marketplace.
     */
    public function browse(string $type = 'all', string $search = '', int $page = 1): array
    {
        if (!$this->isConfigured()) {
            return ['items' => [], 'total' => 0, 'message' => 'License key belum dikonfigurasi.'];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders($this->authHeaders())
                ->get("{$this->baseUrl}/items", [
                    'type' => $type, // 'theme', 'module', 'all'
                    'search' => $search,
                    'page' => $page,
                    'per_page' => 12,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['items' => [], 'total' => 0, 'message' => 'Gagal memuat data: ' . $response->status()];
        } catch (\Exception $e) {
            return ['items' => [], 'total' => 0, 'message' => 'Koneksi gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Get item detail from marketplace.
     */
    public function getItem(string $slug): ?array
    {
        if (!$this->isConfigured())
            return null;

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->authHeaders())
                ->get("{$this->baseUrl}/items/{$slug}");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Download and install an item from marketplace.
     */
    public function install(string $slug, string $type): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'License key belum dikonfigurasi.'];
        }

        try {
            // 1) Request download URL from marketplace
            $response = Http::timeout(15)
                ->withHeaders($this->authHeaders())
                ->post("{$this->baseUrl}/items/{$slug}/download", [
                    'type' => $type,
                    'domain' => request()->getHost(),
                ]);

            if (!$response->successful()) {
                $error = $response->json('message') ?? 'Gagal mendapatkan link download.';
                return ['success' => false, 'message' => $error];
            }

            $downloadUrl = $response->json('download_url');
            if (empty($downloadUrl)) {
                return ['success' => false, 'message' => 'Marketplace tidak memberikan link download.'];
            }

            // 2) Download the ZIP
            $zipResponse = Http::timeout(120)
                ->withHeaders($this->authHeaders())
                ->get($downloadUrl);

            if (!$zipResponse->successful()) {
                return ['success' => false, 'message' => 'Gagal mengunduh file dari marketplace.'];
            }

            // 3) Save to temp file
            $tempFile = tempnam(sys_get_temp_dir(), 'jadiorder_') . '.zip';
            file_put_contents($tempFile, $zipResponse->body());

            // 4) Install using appropriate installer
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile,
                basename($tempFile),
                'application/zip',
                null,
                true
            );

            if ($type === 'theme') {
                $installer = app(ThemeInstallerService::class);
            } else {
                $installer = app(ModuleInstallerService::class);
            }

            $result = $installer->installFromZip($uploadedFile);

            // 5) Cleanup
            @unlink($tempFile);

            // 6) Log the installation
            if ($result['success']) {
                Log::info("Marketplace install: {$type} '{$slug}' installed successfully");
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Marketplace install error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error saat menginstal: ' . $e->getMessage()];
        }
    }

    /**
     * Check for updates for installed items.
     */
    public function checkUpdates(array $installed): array
    {
        if (!$this->isConfigured())
            return [];

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->authHeaders())
                ->post("{$this->baseUrl}/updates/check", [
                    'items' => $installed,
                ]);

            return $response->successful() ? ($response->json('updates') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Auth headers for marketplace API.
     */
    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->licenseKey,
            'X-Domain' => request()->getHost(),
            'X-App-Version' => config('jadiorder.version', '1.0.0'),
            'Accept' => 'application/json',
        ];
    }
}
