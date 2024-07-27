<?php

namespace App\Traits;

trait Res
{
    public function sendRes($message, $status = true,  $response = [], $statusCode = null)
    {
        $responseArray = [
            'status' => $status,
            'message' => $message,
            'response' => $response
        ];

        return $statusCode !== null
            ? response()->json($responseArray, $statusCode)
            : response()->json($responseArray);
    }

    protected function respondWithToken($token, $status = true, $message = '', $response = [], $statusCode = null)
    {
        $responseArray = [
            'status' => $status,
            'message' => $message,
            'token' => $token,
            'response' => $response
        ];

        return $statusCode !== null
            ? response()->json($responseArray, $statusCode)
            : response()->json($responseArray);
    }
}
