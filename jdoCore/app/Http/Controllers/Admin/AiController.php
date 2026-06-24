<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiService;

class AiController extends Controller
{
    public function generateContent(Request $request, GeminiService $gemini)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'type' => 'required|in:blog,page,image_enhancement',
        ]);

        if (!$gemini->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Gemini API Key belum dikonfigurasi. Silakan atur di menu Pengaturan.'], 400);
        }

        // Handle Image Enhancement Suggestion via AI
        if ($request->type === 'image_enhancement') {
            $prompt = "Berdasarkan prompt pengguna terkait foto produk: '{$request->prompt}'. Buatkan 1 paragraf prompt berbahasa Inggris yang sangat detail, profesional, dan fotorealistik untuk digunakan di text-to-image AI (seperti Midjourney atau Nano Banana). Fokuskan pada pencahayaan studio (studio lighting), resolusi tinggi (8k, hyperrealistic), background estetik yang sesuai produk, dan sudut pengambilan gambar yang memikat (commercial product photography). Keluarkan HANYA teks prompt bahasa Inggrisnya saja, tanpa ada basa-basi atau format lain.";
            $result = $gemini->generateText($prompt);

            if ($result) {
                return response()->json(['success' => true, 'content' => $result]);
            }
            $errorMessage = $gemini->getLastError() ?: 'Gagal menghasilkan dari AI.';
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }

        // Handle Blog and Pages Content Generation
        $content = $gemini->generateCustomContent($request->prompt, $request->type);

        if ($content) {
            return response()->json(['success' => true, 'content' => $content]);
        }

        $errorMessage = $gemini->getLastError() ?: 'Gagal menghasilkan konten dari AI. Silakan coba lagi.';
        return response()->json(['success' => false, 'message' => $errorMessage], 500);
    }
}
