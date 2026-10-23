<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Turnstile
{
    public function verify(mixed $token, string $action, ?string $ip): bool
    {
        $verified = false;

        if (is_string($token) && $token !== '' && strlen($token) <= 2048 && config('services.turnstile.secret_key')) {
            try {
                $response = Http::asForm()->connectTimeout(3)->timeout(10)->post(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                    [
                        'secret' => config('services.turnstile.secret_key'),
                        'response' => $token,
                        'remoteip' => $ip,
                    ],
                );

                $verified = $response->successful()
                    && $response->json('success') === true
                    && $response->json('action') === $action
                    && $response->json('hostname') === config('services.turnstile.hostname');
            } catch (ConnectionException) {
                // A verification outage must not bypass the challenge.
            }
        }

        return $verified;
    }
}
