<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = 'Request Successfull', $statusCode = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message ? $message : 'Request Successfull',
            'data' => $data,
        ], $statusCode);
    }

    public static function error($message = 'Request Failed', $statusCode = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message ? $message : 'Request Failed',
        ], $statusCode);
    }
}