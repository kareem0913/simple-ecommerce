<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebHooksController extends Controller
{
    public function orderStatus(Request $request)
    {
        Log::info(['Webhook received:' => $request->all()]);
        return response()->json([
            'status' => 'success',
            'message' => 'Order status received successfully.',
        ]);
    }
}
