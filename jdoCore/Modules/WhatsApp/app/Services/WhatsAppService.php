<?php

namespace Modules\WhatsApp\app\Services;

use App\Contracts\WhatsAppInterface;
use App\Services\SettingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService implements WhatsAppInterface
{
    protected SettingService $settings;

    public function __construct(SettingService $settings)
    {
        $this->settings = $settings;
    }

    public function getName(): string
    {
        return 'Custom WAGW';
    }

    public function isConfigured(): bool
    {
        return !empty($this->settings->get('wagw_domain'))
            && !empty($this->settings->get('wagw_nomer'));
    }

    public function send(string $number, string $message, ?string $img = null): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('WhatsApp Gateway not configured, message not sent.');
            return false;
        }

        // Clean number
        $nomerStr = preg_replace('/[^0-9]/', '', $number);
        
        // Convert country code 62 back to 0 as expected by WAGW local format
        if (str_starts_with($nomerStr, '62')) {
            $nomerStr = '0' . substr($nomerStr, 2);
        }
        
        $nomerInt = intval($nomerStr);
        $cleanNumber = (substr((string)$nomerInt, 0, 1) != "0") ? "0" . $nomerInt : (string)$nomerInt;

        $pesan = ltrim($message);
        $domainRaw = rtrim($this->settings->get('wagw_domain'), '/');
        $domain = $domainRaw . '/';
        $url = $domain . 'send-message';

        $data = [
            "sender" => $this->settings->get('wagw_nomer'),
            "number" => $cleanNumber,
            "message" => $pesan
        ];

        if ($img != null) {
            $url = $domain . 'send-media';
            $a = explode('/', $img);
            $filename = $a[count($a) - 1];
            $a2 = explode('.', $filename);
            
            // Handle possibility of files with NO extension gently
            $namefile = count($a2) >= 2 ? $a2[count($a2) - 2] : $filename;
            $ext = count($a2) >= 2 ? $a2[count($a2) - 1] : 'jpg'; // Fallback
            
            unset($data["message"]);
            $data['caption'] = $pesan;
            $data['url'] = $img;
            $data['filename'] = $namefile;
            $data['filetype'] = $ext;
        }

        try {
            $response = Http::asForm()->timeout(10)->post($url, $data);

            if ($response->successful()) {
                return true;
            }

            Log::error("WhatsApp Gateway error [{$response->status()}]: {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp Gateway send failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendTemplate(string $number, string $template, array $params = []): bool
    {
        $message = $template;
        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        return $this->send($number, $message);
    }

    public function test(string $number, string $message): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp Gateway not configured.'
            ];
        }

        $nomerStr = preg_replace('/[^0-9]/', '', $number);
        
        // Convert country code 62 back to 0 for WAGW
        if (str_starts_with($nomerStr, '62')) {
            $nomerStr = '0' . substr($nomerStr, 2);
        }
        
        $nomerInt = intval($nomerStr);
        $cleanNumber = (substr((string)$nomerInt, 0, 1) != "0") ? "0" . $nomerInt : (string)$nomerInt;

        $pesan = ltrim($message);
        $domainRaw = rtrim($this->settings->get('wagw_domain'), '/');
        $domain = $domainRaw . '/';
        $url = $domain . 'send-message';

        $data = [
            "sender" => $this->settings->get('wagw_nomer'),
            "number" => $cleanNumber,
            "message" => $pesan
        ];

        try {
            $response = Http::asForm()->timeout(10)->post($url, $data);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
                'payload' => $data,
                'url' => $url
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'url' => $url
            ];
        }
    }
}
