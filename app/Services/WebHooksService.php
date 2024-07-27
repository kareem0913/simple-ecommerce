<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebHooksService
{
    public function orderStatus($data)
    {
        $response = Http::post(env('WEB_HOOKS_URL'), $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error communicating with webhook: ' . $response->body());
    }
}
