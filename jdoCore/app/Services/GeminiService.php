<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl;
    protected $lastError = null;

    public function __construct(SettingService $settingService)
    {
        $this->apiKey = $settingService->get('gemini_api_key');
        $this->model = $settingService->get('gemini_model', 'gemini-1.5-flash');

        // Map model to correct API version
        // 1.5, 2.x, and 3.x models often use v1beta for advanced features
        $version = 'v1';
        if (preg_match('/(1\.5|2\.|3\.)/', $this->model)) {
            $version = 'v1beta';
        }

        $this->baseUrl = "https://generativelanguage.googleapis.com/{$version}/models/{$this->model}:generateContent";
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function generateText(string $prompt): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '?key=' . $this->apiKey, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 8192,
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            $errorData = $response->json();
            $this->lastError = $errorData['error']['message'] ?? $response->body();
            Log::error('Gemini API Error: ' . $this->lastError);
            return null;

        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error('Gemini API Exception: ' . $this->lastError);
            return null;
        }
    }

    public function generateProductDescription(string $productName, string $categoryName): ?string
    {
        $prompt = "Buatkan deskripsi produk marketing berbahasa Indonesia yang sangat menarik, persuasif, dan profesional untuk produk bernama '{$productName}' dalam kategori '{$categoryName}'. Deskripsi harus terdiri dari 2-3 paragraf. Gunakan format HTML paragraf (<p>) dan daftar (<ul><li>) jika perlu menjelaskan fitur bayangan. Jangan sertakan judul H1/H2, langsung intisari saja.";
        return $this->generateText($prompt);
    }

    public function getSmartBusinessInsight(array $stats): ?string
    {
        $prompt = "Berikut adalah data ringkasan penjualan toko online saya bulan ini:\n"
            . "- Total Pesanan: {$stats['total_orders']}\n"
            . "- Pendapatan: Rp " . number_format($stats['revenue'], 0, ',', '.') . "\n"
            . "- Pesanan Baru Hari Ini: {$stats['orders_today']}\n"
            . "- Pelanggan Aktif: {$stats['active_customers']}\n\n"
            . "Sebagai seorang konsultan bisnis ritel profesional, berikan 1 paragraf wawasan singkat (maksimal 3 kalimat) berisi pujian dan 1 saran strategis (actionable advice) yang spesifik untuk meningkatkan penjualan saya ke depannya berdasarkan data di atas. Jangan pakai sapaan, langsung to the point.";
        return $this->generateText($prompt);
    }

    public function generateCustomContent(string $prompt, string $type = 'blog'): ?string
    {
        $context = $type === 'blog'
            ? "Tuliskan artikel blog yang SEO-friendly, menarik, dan terstruktur. Gunakan format HTML yang rapi (<h2>, <p>, <ul>). Jangan sertakan tag <html> atau <body>, langsung isinya saja."
            : "Tuliskan konten halaman website yang profesional, informatif, dan persuasif. Gunakan format HTML yang rapi (<h2>, <p>, <ul>). Jangan sertakan tag <html> atau <body>, langsung isinya saja.";

        $fullPrompt = "{$context}\n\nTopik/Perintah tambahan: {$prompt}";

        return $this->generateText($fullPrompt);
    }
}
