<?php

namespace App\Services;

use App\Contracts\CourierInterface;

class ShippingService
{
    public function __construct(protected SettingService $settings)
    {
    }

    /**
     * Active courier provider key from settings.
     */
    public function activeProviderKey(): string
    {
        $key = (string) $this->settings->get('active_courier_provider', 'rajaongkir');
        return $key !== '' ? $key : 'rajaongkir';
    }

    /**
     * Resolve courier provider by key (defaults to active one).
     */
    public function resolveProvider(?string $provider = null): ?CourierInterface
    {
        $provider = $provider ?: $this->activeProviderKey();
        $binding = "courier.provider.{$provider}";

        if (app()->bound($binding)) {
            return app($binding);
        }

        // Backward-compat: legacy generic CourierInterface binding
        if (app()->bound(CourierInterface::class)) {
            return app(CourierInterface::class);
        }

        return null;
    }

    /**
     * Get all registered courier providers (for admin dropdown).
     *
     * @return array<string, CourierInterface>
     */
    public function availableProviders(): array
    {
        $known = ['rajaongkir', 'kiriminaja'];
        $result = [];
        foreach ($known as $key) {
            $binding = "courier.provider.{$key}";
            if (app()->bound($binding)) {
                $result[$key] = app($binding);
            }
        }
        return $result;
    }

    /**
     * Get shipping rates from the active provider.
     */
    public function getRates(array $origin, array $destination, int $weight, ?string $courier = null): array
    {
        $provider = $this->resolveProvider();

        if (!$provider) {
            return [];
        }

        return $provider->getRates($origin, $destination, $weight, $courier);
    }

    /**
     * Search destination via active provider.
     */
    public function searchDestination(string $search): array
    {
        $provider = $this->resolveProvider();

        if (!$provider) {
            return [];
        }

        return $provider->searchDestination($search);
    }

    /**
     * Track a shipment via active provider.
     */
    public function track(string $courier, string $trackingNumber): array
    {
        $provider = $this->resolveProvider();

        if (!$provider) {
            return ['error' => 'Courier provider not configured'];
        }

        return $provider->track($courier, $trackingNumber);
    }
}
