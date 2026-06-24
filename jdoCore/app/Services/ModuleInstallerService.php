<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class ModuleInstallerService
{
    /**
     * Required files in a module ZIP.
     */
    protected array $requiredFiles = [
        'module.json',
    ];

    /**
     * Required fields in module.json.
     */
    protected array $requiredJsonFields = ['name', 'alias', 'providers'];

    /**
     * Validate and install a module from a ZIP file.
     *
     * @return array{success: bool, message: string, name?: string, warnings?: string[]}
     */
    public function installFromZip(UploadedFile $file): array
    {
        $zip = new ZipArchive;
        $tempPath = $file->getPathname();

        if ($zip->open($tempPath) !== TRUE) {
            return ['success' => false, 'message' => 'Gagal membuka file ZIP. Pastikan file tidak corrupt.'];
        }

        // 1) Detect module root folder
        $root = $this->detectRootFolder($zip);
        if ($root === null) {
            $zip->close();
            return ['success' => false, 'message' => 'Struktur ZIP tidak valid. File harus berisi folder modul dengan module.json.'];
        }

        // 2) Validate required files
        $validation = $this->validateStructure($zip, $root);
        if (!$validation['valid']) {
            $zip->close();
            return ['success' => false, 'message' => $validation['message']];
        }

        // 3) Read and validate module.json
        $moduleContent = $zip->getFromName($root . 'module.json');
        $moduleJson = json_decode($moduleContent, true);

        if (!$moduleJson) {
            $zip->close();
            return ['success' => false, 'message' => 'module.json bukan JSON yang valid.'];
        }

        $missingFields = [];
        foreach ($this->requiredJsonFields as $field) {
            if (empty($moduleJson[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $zip->close();
            return [
                'success' => false,
                'message' => 'module.json harus berisi field: ' . implode(', ', $missingFields),
            ];
        }

        $moduleName = $moduleJson['name'];

        // 4) Validate namespace convention
        if (!preg_match('/^[A-Z][a-zA-Z0-9]+$/', $moduleName)) {
            $zip->close();
            return [
                'success' => false,
                'message' => "Nama modul \"{$moduleName}\" tidak valid. Harus PascalCase (contoh: PaymentGateway).",
            ];
        }

        // 5) Validate provider class references
        foreach ($moduleJson['providers'] as $provider) {
            if (!str_starts_with($provider, 'Modules\\')) {
                $zip->close();
                return [
                    'success' => false,
                    'message' => "Provider \"{$provider}\" harus menggunakan namespace Modules\\\\.",
                ];
            }
        }

        // 6) Check for expected directory structure
        $warnings = [];
        $expectedDirs = ['app/', 'config/', 'routes/'];
        foreach ($expectedDirs as $dir) {
            $found = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (str_starts_with($zip->getNameIndex($i), $root . $dir)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $warnings[] = "Direktori opsional tidak ditemukan: {$dir}";
            }
        }

        // 7) Security scan
        $securityCheck = $this->securityScan($zip, $root);
        if (!$securityCheck['safe']) {
            $zip->close();
            return ['success' => false, 'message' => 'Modul ditolak: ' . $securityCheck['reason']];
        }

        // 8) Extract to Modules directory
        $targetPath = base_path('Modules/' . $moduleName);
        $isUpdate = File::isDirectory($targetPath);

        if ($isUpdate) {
            File::deleteDirectory($targetPath);
        }

        File::ensureDirectoryExists($targetPath);
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (!str_starts_with($entryName, $root))
                continue;

            $relativePath = substr($entryName, strlen($root));
            if (empty($relativePath))
                continue;

            $destPath = $targetPath . '/' . $relativePath;

            if (str_ends_with($entryName, '/')) {
                File::ensureDirectoryExists($destPath);
            } else {
                File::ensureDirectoryExists(dirname($destPath));
                file_put_contents($destPath, $zip->getFromIndex($i));
            }
        }

        $zip->close();

        // 9) Run post-install commands
        try {
            Artisan::call('module:enable', ['module' => $moduleName]);
        } catch (\Exception $e) {
            $warnings[] = 'Modul diextract tapi gagal di-enable: ' . $e->getMessage();
        }

        return [
            'success' => true,
            'message' => $isUpdate
                ? "Modul \"{$moduleName}\" berhasil diperbarui!"
                : "Modul \"{$moduleName}\" berhasil diinstal!",
            'name' => $moduleName,
            'warnings' => $warnings,
        ];
    }

    /**
     * Detect root folder containing module.json.
     */
    protected function detectRootFolder(ZipArchive $zip): ?string
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === 'module.json' && substr_count($name, '/') <= 1) {
                $parts = explode('/', $name);
                return count($parts) === 2 ? $parts[0] . '/' : '';
            }
        }
        return null;
    }

    /**
     * Validate required files exist.
     */
    protected function validateStructure(ZipArchive $zip, string $root): array
    {
        foreach ($this->requiredFiles as $required) {
            $found = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ($zip->getNameIndex($i) === $root . $required) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return ['valid' => false, 'message' => "File wajib tidak ditemukan: {$required}"];
            }
        }
        return ['valid' => true];
    }

    /**
     * Security scan for dangerous patterns.
     */
    protected function securityScan(ZipArchive $zip, string $root): array
    {
        $dangerous = [
            '/\beval\s*\(/i',
            '/\bshell_exec\s*\(/i',
            '/\bpassthru\s*\(/i',
            '/\bproc_open\s*\(/i',
            '/\b__halt_compiler\b/i',
        ];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!str_starts_with($name, $root))
                continue;
            if (!str_ends_with($name, '.php'))
                continue;

            $content = $zip->getFromIndex($i);
            foreach ($dangerous as $pattern) {
                if (preg_match($pattern, $content)) {
                    return ['safe' => false, 'reason' => "File '{$name}' mengandung kode berbahaya."];
                }
            }
        }

        return ['safe' => true];
    }
}
