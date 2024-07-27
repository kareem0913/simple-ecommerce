<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebHooksService
{
    public function orderStatus($data)
    {
        $response = Http::post('https://webhook.site/881a5e9e-6758-4712-b05e-a6fb6ce75c51', $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error communicating with webhook: ' . $response->body());
    }
}
