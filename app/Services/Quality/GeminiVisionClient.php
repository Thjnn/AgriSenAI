<?php

namespace App\Services\Quality;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;
use RuntimeException;

/**
 * Goi Gemini Vision API.
 *
 * Chi lam MOT viec: gui prompt + anh, nhan ve mang da decode.
 * KHONG phan quyet hang — viec do la cua GradeRuleEngine.
 */
class GeminiVisionClient
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * @param  array  $turns   Cac luot hoi-thoai (few-shot + anh that)
     * @param  array  $schema  Schema ep dinh dang JSON tra ve
     *
     * @return array Ket qua da decode tu JSON
     */
    public function assess(string $systemInstruction, array $turns, array $schema): array
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            throw new RuntimeException(
                'Thieu GEMINI_API_KEY trong .env. Lay key tai https://aistudio.google.com/apikey'
            );
        }

        $model = config('services.gemini.model');

        $response = Http::timeout(config('services.gemini.timeout'))
            ->withHeaders($this->authHeader($key))
            ->acceptJson()
            // 503 = model qua tai, 429 = cham nguong goi. Ca hai deu TAM THOI:
            // cho roi goi lai la duoc, khong phai loi cua ta.
            ->retry(4, 3000, fn ($e, $req) => $e instanceof ConnectionException
                || in_array($e->response?->status(), [429, 500, 503], true), throw: false)
            ->post(self::ENDPOINT."/{$model}:generateContent", [
                'system_instruction' => [
                    'parts' => [['text' => $systemInstruction]],
                ],
                'contents' => $turns,
                'generationConfig' => [
                    // Cham chat luong can ON DINH: cung mot anh phai ra cung mot hang.
                    // Nhiet do cao se lam ket qua nhay lung tung giua cac lan chay.
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $schema,
                ],
            ]);

        if ($response->failed()) {
            $msg = $response->json('error.message') ?? $response->body();

            if ($response->status() === 503) {
                $msg .= " (Model '{$model}' dang qua tai. Doi vai phut, hoac doi GEMINI_MODEL "
                    .'trong .env sang model khac — vd gemini-3.1-flash-lite.)';
            }

            throw new RuntimeException("Gemini API loi {$response->status()}: {$msg}");
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (blank($text)) {
            $reason = $response->json('candidates.0.finishReason') ?? 'khong ro';

            throw new RuntimeException("Gemini khong tra ve noi dung (finishReason: {$reason}).");
        }

        try {
            return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Gemini tra ve JSON hong: {$e->getMessage()}");
        }
    }

    /**
     * API key gui qua header `x-goog-api-key` (khong dat tren URL de khoi lot
     * vao log truy cap).
     *
     * Luu y: Google co HAI dinh dang API key dang luu hanh — kieu cu `AIza...`
     * va kieu moi `AQ....`. CA HAI deu dung header nay. Dung nham nhan dang
     * key kieu moi la OAuth token roi gui `Authorization: Bearer` — se dinh 401.
     */
    private function authHeader(string $key): array
    {
        return ['x-goog-api-key' => $key];
    }
}
