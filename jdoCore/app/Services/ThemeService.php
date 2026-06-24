<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;

class ThemeService
{
    protected string $themesPath;

    public function __construct()
    {
        $this->themesPath = resource_path('themes');
    }

    /**
     * Get active theme slug.
     */
    public function getActiveTheme(): string
    {
        if (!file_exists(storage_path('installed'))) {
            return 'default';
        }

        try {
            return Setting::getValue('active_theme', 'default');
        } catch (\Exception $e) {
            return 'default';
        }
    }

    /**
     * Set active theme.
     */
    public function setActiveTheme(string $slug): void
    {
        Setting::setValue('active_theme', $slug, 'store', 'string');
    }

    /**
     * Register theme view namespace.
     */
    public function registerViews(): void
    {
        $theme = $this->getActiveTheme();
        $path = $this->themesPath . '/' . $theme;

        if (File::isDirectory($path)) {
            view()->addNamespace('theme', $path);
        } else {
            // Fallback to default
            view()->addNamespace('theme', $this->themesPath . '/default');
        }
    }

    /**
     * Get theme config.
     */
    public function getThemeConfig(?string $slug = null): array
    {
        $slug = $slug ?? $this->getActiveTheme();
        $configPath = $this->themesPath . '/' . $slug . '/config.json';

        if (File::exists($configPath)) {
            return json_decode(File::get($configPath), true) ?? [];
        }

        return [];
    }

    /**
     * Get all available themes.
     */
    public function getAvailableThemes(): array
    {
        $themes = [];

        if (!File::isDirectory($this->themesPath)) {
            return $themes;
        }

        foreach (File::directories($this->themesPath) as $dir) {
            $slug = basename($dir);
            $config = $this->getThemeConfig($slug);
            $themes[] = [
                'slug' => $slug,
                'name' => $config['name'] ?? ucfirst($slug),
                'version' => $config['version'] ?? '1.0.0',
                'description' => $config['description'] ?? '',
                'thumbnail' => $config['thumbnail'] ?? null,
                'active' => $slug === $this->getActiveTheme(),
            ];
        }

        return $themes;
    }
}
