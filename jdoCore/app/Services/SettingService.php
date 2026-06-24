<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Curated storefront color palette options.
     *
     * Used by admin settings so users choose from tasteful presets instead of
     * entering arbitrary hex values that could break contrast/readability.
     *
     * @return array<string, array{name:string,hex:string,group:string}>
     */
    public function curatedColorPalette(): array
    {
        return [
            'indigo'        => ['name' => 'Indigo Royal',      'hex' => '#4F46E5', 'group' => 'elegant'],
            'blue'          => ['name' => 'Ocean Blue',        'hex' => '#2563EB', 'group' => 'classic'],
            'sky'           => ['name' => 'Sky Glass',         'hex' => '#0EA5E9', 'group' => 'fresh'],
            'teal'          => ['name' => 'Teal Leaf',         'hex' => '#0F766E', 'group' => 'fresh'],
            'emerald'       => ['name' => 'Emerald Calm',      'hex' => '#059669', 'group' => 'fresh'],
            'sage'          => ['name' => 'Pastel Sage',       'hex' => '#84A98C', 'group' => 'pastel'],
            'mint'          => ['name' => 'Mint Frost',        'hex' => '#6FCF97', 'group' => 'pastel'],
            'olive'         => ['name' => 'Olive Luxe',        'hex' => '#6B8E23', 'group' => 'earthy'],
            'gold'          => ['name' => 'Soft Gold',         'hex' => '#C59D5F', 'group' => 'luxury'],
            'amber'         => ['name' => 'Amber Warm',        'hex' => '#D97706', 'group' => 'warm'],
            'terracotta'    => ['name' => 'Terracotta',        'hex' => '#C56B46', 'group' => 'warm'],
            'coral'         => ['name' => 'Coral Bloom',       'hex' => '#F97360', 'group' => 'pastel'],
            'rose'          => ['name' => 'Rose Blush',        'hex' => '#E11D48', 'group' => 'romantic'],
            'pink'          => ['name' => 'Dusty Pink',        'hex' => '#EC4899', 'group' => 'pastel'],
            'lavender'      => ['name' => 'Lavender Mist',     'hex' => '#A78BFA', 'group' => 'pastel'],
            'purple'        => ['name' => 'Velvet Purple',     'hex' => '#7C3AED', 'group' => 'elegant'],
            'navy'          => ['name' => 'Deep Navy',         'hex' => '#1E3A8A', 'group' => 'classic'],
            'slate'         => ['name' => 'Slate Modern',      'hex' => '#475569', 'group' => 'neutral'],
            'charcoal'      => ['name' => 'Charcoal Luxe',     'hex' => '#334155', 'group' => 'neutral'],
            'chocolate'     => ['name' => 'Chocolate Mocha',   'hex' => '#7C4A2D', 'group' => 'earthy'],
        ];
    }

    /**
     * Get a setting value with cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            return Setting::getValue($key, $default);
        });
    }

    /**
     * Set a setting value and clear cache.
     */
    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        Setting::setValue($key, $value, $group, $type);
        Cache::forget("setting.{$key}");
        Cache::forget("settings.group.{$group}");
    }

    /**
     * Get all settings for a group.
     */
    public function getGroup(string $group): array
    {
        return Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            return Setting::getGroup($group);
        });
    }

    /**
     * Clear all settings cache.
     */
    public function clearCache(): void
    {
        $settings = Setting::all();
        foreach ($settings as $setting) {
            Cache::forget("setting.{$setting->key}");
        }
        $groups = ['general', 'store', 'payment', 'shipping', 'notification', 'whatsapp', 'ai'];
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }
    }

    /**
     * Get store name (dynamic).
     */
    public function storeName(): string
    {
        return $this->get('store_name', config('jadiorder.store_name'));
    }

    /**
     * Get primary color.
     */
    public function primaryColor(): string
    {
        $palette = $this->curatedColorPalette();
        $selected = (string) $this->get('theme_primary_palette', '');

        if ($selected !== '' && isset($palette[$selected])) {
            return $palette[$selected]['hex'];
        }

        return $this->get('primary_color', config('jadiorder.primary_color'));
    }

    /**
     * Get secondary color.
     */
    public function secondaryColor(): string
    {
        $palette = $this->curatedColorPalette();
        $selected = (string) $this->get('theme_secondary_palette', '');

        if ($selected !== '' && isset($palette[$selected])) {
            return $palette[$selected]['hex'];
        }

        return $this->get('secondary_color', config('jadiorder.secondary_color'));
    }

    /**
     * Get phone country code (default 62).
     */
    public function countryCode(): string
    {
        $code = $this->get('phone_country_code', '62');
        
        // Sanitize: Only digits
        $code = preg_replace('/[^0-9]/', '', (string) $code);
        
        // Fallback to 62 if empty or if admin accidentally entered a full phone number (length > 3)
        if (empty($code) || strlen($code) > 3) {
            return '62';
        }
        
        return $code;
    }

    /**
     * Normalize phone number to international format.
     */
    public function formatPhone(string $phone): string
    {
        // 1. Remove all non-numeric characters (including +)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $code = $this->countryCode();

        // 2. If starts with 0, replace 0 with country code
        if (str_starts_with($phone, '0')) {
            return $code . substr($phone, 1);
        }

        // 3. If it already starts with the country code, return as is
        if (str_starts_with($phone, $code)) {
            return $phone;
        }

        // 4. If it's a "clean" number but missing country code (e.g., 812... instead of 0812...)
        // We only prepend if it looks like a local number (usually starts with 8 for ID)
        if (strlen($phone) >= 9 && strlen($phone) <= 13) {
            return $code . $phone;
        }

        return $phone;
    }
}
