<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    private $secretKey;
    private $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct()
    {
        // Используем тестовый ключ, если не указан реальный
        $this->secretKey = env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
    }

    /**
     * Verify reCAPTCHA token
     *
     * @param string $token
     * @param string|null $remoteIp
     * @return bool
     */
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        if (empty($this->secretKey)) {
            Log::warning('RECAPTCHA_SECRET_KEY is not set');
            return false;
        }

        if (empty($token)) {
            Log::warning('reCAPTCHA token is empty');
            return false;
        }

        try {
            $response = Http::asForm()->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);

            $result = $response->json();

            // Логируем результат для отладки
            if (!isset($result['success']) || $result['success'] !== true) {
                Log::warning('reCAPTCHA verification failed', [
                    'result' => $result,
                    'token_length' => strlen($token),
                ]);
            }

            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error: ' . $e->getMessage());
            return false;
        }
    }
}

