<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function verify(?string $token): bool
    {
        if (! config('recaptcha.enabled') || empty(config('recaptcha.secret_key'))) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(self::VERIFY_URL, [
                    'secret' => config('recaptcha.secret_key'),
                    'response' => $token,
                ]);

            if (! $response->successful()) {
                return false;
            }

            $data = $response->json();

            return ($data['success'] ?? false)
                && ($data['score'] ?? 0) >= config('recaptcha.score_threshold');
        } catch (ConnectionException $e) {
            Log::warning('reCAPTCHA verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
