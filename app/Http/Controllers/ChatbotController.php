<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Menampilkan halaman Asisten Informasi SPPG.
     */
    public function index()
    {
        return view('chatbot.index');
    }

    /**
     * Meneruskan pertanyaan pengguna ke layanan FastAPI.
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:3000'],
        ]);

        $serviceUrl = rtrim((string) config('services.sppg_ai.url'), '/');
        $serviceToken = (string) config('services.sppg_ai.token');

        if ($serviceUrl === '' || $serviceToken === '') {
            Log::error('SPPG AI configuration is incomplete.');

            return response()->json([
                'status' => 'error',
                'message' => 'Konfigurasi layanan asisten informasi belum lengkap.',
            ], 503);
        }

        try {
            $response = Http::connectTimeout(10)
                ->timeout(120)
                ->acceptJson()
                ->withHeaders([
                    'X-Internal-Token' => $serviceToken,
                ])
                ->post($serviceUrl . '/chat', [
                    'question' => $validated['question'],
                    'history' => $validated['history'] ?? [],
                ]);

            if (!$response->successful()) {
                Log::error('SPPG AI Service returned an unsuccessful response.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Asisten informasi belum dapat memproses pertanyaan. Silakan coba kembali.',
                ], 502);
            }

            $data = $response->json();

            if (!is_array($data) || !isset($data['answer'])) {
                Log::error('SPPG AI Service returned an unexpected response format.', [
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Format jawaban dari layanan AI tidak sesuai.',
                ], 502);
            }

            return response()->json([
                'status' => $data['status'] ?? 'success',
                'answer' => $data['answer'],
                'model' => $data['model'] ?? null,
                'sources' => is_array($data['sources'] ?? null) ? $data['sources'] : [],
            ]);
        } catch (\Throwable $e) {
            Log::error('SPPG chatbot connection error.', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Layanan asisten informasi sedang tidak tersedia. Pastikan server AI sedang berjalan.',
            ], 503);
        }
    }
}
