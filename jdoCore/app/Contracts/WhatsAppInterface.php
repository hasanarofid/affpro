<?php

namespace App\Contracts;

interface WhatsAppInterface
{
    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Send a plain text or media message.
     */
    public function send(string $number, string $message, ?string $img = null): bool;

    /**
     * Send a template message with parameters.
     */
    public function sendTemplate(string $number, string $template, array $params = []): bool;

    /**
     * Check if the provider is properly configured.
     */
    public function isConfigured(): bool;

    /**
     * Send a test message and return raw response data.
     */
    public function test(string $number, string $message): array;
}
