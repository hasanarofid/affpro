<?php

namespace App\Contracts;

class PaymentResponse
{
    public function __construct(
        public bool $success,
        public string $message = '',
        public ?string $redirectUrl = null,
        public ?string $providerRef = null,
        public array $metadata = [],
    ) {
    }
}
