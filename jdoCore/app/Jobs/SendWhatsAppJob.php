<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Contracts\WhatsAppInterface;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;
    public $message;
    public $imageUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $message, ?string $imageUrl = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->imageUrl = $imageUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppInterface $wa): void
    {
        try {
            if ($wa->isConfigured()) {
                $wa->send($this->phone, $this->message, $this->imageUrl);
            }
        } catch (\Exception $e) {
            Log::error('SendWhatsAppJob failed: ' . $e->getMessage());
            // Retry on failure if needed by throwing the exception
            // throw $e; 
        }
    }
}
