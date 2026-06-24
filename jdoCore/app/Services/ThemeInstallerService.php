<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ThemeInstallerService
{
    /**
     * Required files in a theme ZIP.
     */
    protected array $requiredFiles = [
        'config.json',
        'layouts/app.blade.php',
    ];

    /**
     * Optional but expected files.
     */
    protected array $expectedFiles = [
        'home.blade.php',
        'products/index.blade.php',
        'products/show.blade.php',
    ];

    /**
     * Validate and install a theme from a ZIP file.
     *
     * @return array{success: bool, message: string, slug?: string, warnings?: string[]}
     */
    public function installFromZip(UploadedFile $file): array
    {
        $zip = new ZipArchive;
        $tempPath = $file->getPathname();

        if ($zip->open($tempPath) !== TRUE) {
            return ['success' => false, 'message' => 'Gagal membuka file ZIP. Pastikan file tidak corrupt.'];
        }

        // 1) Detect theme root folder inside ZIP
        $root = $this->detectRootFolder($zip);
        if ($root === null) {
            $zip->close();
            return ['success' => false, 'message' => 'Struktur ZIP tidak valid. File harus berisi satu folder tema utama.'];
        }

        // 2) Validate required files exist
        $validation = $this->validateStructure($zip, $root);
        if (!$validation['valid']) {
            $zip->close();
            return ['success' => false, 'message' => $validation['message']];
        }

        // 3) Read and validate config.json
        $configContent = $zip->getFromName($root . 'config.json');
        $config = json_decode($configContent, true);

        if (!$config || empty($config['name'])) {
            $zip->close();
            return ['success' => false, 'message' => 'config.json tidak valid. Harus berisi minimal field "name".'];
        }

        // 4) Determine slug from folder name
        $slug = basename(rtrim($root, '/'));
        if (empty($slug) || $slug === '.') {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '-', $config['name']));
        }

        // 5) Security check — prevent path traversal
        if (str_contains($slug, '..') || str_contains($slug, '/') || str_contains($slug, '\\')) {
            $zip->close();
            return ['success' => false, 'message' => 'Nama tema mengandung karakter tidak valid.'];
        }

        // 6) Check for malicious PHP code patterns
        $securityCheck = $this->securityScan($zip, $root);
        if (!$securityCheck['safe']) {
            $zip->close();
            return ['success' => false, 'message' => 'Tema ditolak: ' . $securityCheck['reason']];
        }

        // 7) Extract to themes directory
        $targetPath = resource_path('themes/' . $slug);
        $publicPath = public_path('themes/' . $slug);
        $isUpdate = File::isDirectory($targetPath);

        if ($isUpdate) {
            File::deleteDirectory($targetPath);
            if (File::isDirectory($publicPath)) {
                File::deleteDirectory($publicPath);
            }
        }

        File::ensureDirectoryExists($targetPath);
        File::ensureDirectoryExists($publicPath);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            // Skip if not inside our root
            if (!str_starts_with($entryName, $root))
                continue;

            $relativePath = substr($entryName, strlen($root));
            if (empty($relativePath))
                continue;

            // Determine if it should go to public or resources
            if (str_starts_with($relativePath, 'assets/')) {
                // Extract to public, removing 'assets/' prefix
                $assetRelative = substr($relativePath, 7);
                if (empty($assetRelative))
                    continue;
                $destPath = $publicPath . '/' . $assetRelative;
            } else {
                // Extract to resources
                $destPath = $targetPath . '/' . $relativePath;
            }

            if (str_ends_with($entryName, '/')) {
                File::ensureDirectoryExists($destPath);
            } else {
                File::ensureDirectoryExists(dirname($destPath));
                file_put_contents($destPath, $zip->getFromIndex($i));
            }
        }

        $zip->close();

        // 8) Check for missing optional files (warnings)
        $warnings = [];
        foreach ($this->expectedFiles as $expected) {
            if (!File::exists($targetPath . '/' . $expected)) {
                $warnings[] = "File opsional tidak ditemukan: {$expected}";
            }
        }

        return [
            'success' => true,
            'message' => $isUpdate
                ? "Tema \"{$config['name']}\" berhasil diperbarui!"
                : "Tema \"{$config['name']}\" berhasil diinstal!",
            'slug' => $slug,
            'warnings' => $warnings,
        ];
    }

    /**
     * Detect the root folder inside the ZIP.
     */
    protected function detectRootFolder(ZipArchive $zip): ?string
    {
        // Look for config.json — it should be at {root}/config.json
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === 'config.json' && substr_count($name, '/') <= 1) {
                $parts = explode('/', $name);
                if (count($parts) === 2) {
                    return $parts[0] . '/'; // e.g. "mytheme/"
                } elseif (count($parts) === 1) {
                    return ''; // config.json at root
                }
            }
        }

        return null;
    }

    /**
     * Validate required file structure.
     */
    protected function validateStructure(ZipArchive $zip, string $root): array
    {
        $missing = [];

        foreach ($this->requiredFiles as $required) {
            $found = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name === $root . $required) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missing[] = $required;
            }
        }

        if (!empty($missing)) {
            return [
                'valid' => false,
                'message' => 'File wajib tidak ditemukan: ' . implode(', ', $missing) . '. Struktur tema tidak sesuai.',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Basic security scan for dangerous PHP patterns.
     */
    protected function securityScan(ZipArchive $zip, string $root): array
    {
        $dangerousPatterns = [
            '/\beval\s*\(/i',
            '/\bexec\s*\(/i',
            '/\bsystem\s*\(/i',
            '/\bpassthru\s*\(/i',
            '/\bshell_exec\s*\(/i',
            '/\bproc_open\s*\(/i',
            '/\bbase64_decode\s*\(\s*\$/',
            '/\bfile_put_contents\s*\(/i',
            '/\b__halt_compiler\b/i',
        ];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!str_starts_with($name, $root))
                continue;
            if (!str_ends_with($name, '.php') && !str_ends_with($name, '.blade.php'))
                continue;

            $content = $zip->getFromIndex($i);
            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return [
                        'safe' => false,
                        'reason' => "File '{$name}' mengandung kode berbahaya yang tidak diizinkan.",
                    ];
                }
            }
        }

        return ['safe' => true];
    }
}
