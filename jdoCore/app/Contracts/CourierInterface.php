<?php

namespace App\Contracts;

interface CourierInterface
{
    /**
     * Get the provider display name.
     */
    public function getName(): string;

    /**
     * Provider key/slug used for binding lookup (e.g. 'rajaongkir', 'kiriminaja').
     */
    public function getProviderKey(): string;

    /**
     * Get available shipping rates.
     *
     * Returns flat list with at least:
     * courier_code, courier_name, service, description, cost, etd
     */
    public function getRates(array $origin, array $destination, int $weight, ?string $courier = null): array;

    /**
     * Search destination (kecamatan/city) by keyword.
     */
    public function searchDestination(string $search): array;

    /**
     * Track a shipment by tracking number.
     */
    public function track(string $courier, string $trackingNumber): array;

    /**
     * Check if the provider is properly configured.
     */
    public function isConfigured(): bool;
}
