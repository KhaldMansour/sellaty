<?php

namespace App\Helpers;

class ApiResponse
{
    public static function send($status, $error = null, $message = '', $data = null, $code = 200)
    {
        return response()->json([
            'status' => $status,
            'error' => $error,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
