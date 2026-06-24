<?php

namespace App\Contracts;

class CallbackResponse
{
    public function __construct(
        public bool $success,
        public string $orderNumber = '',
        public string $status = '',  // 'paid', 'failed', 'expired'
        public string $message = '',
        public array $metadata = [],
    ) {
    }
}
