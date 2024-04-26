<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public static function response($result, $message, $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $result,
            'message' => __($message),
            'success' => true,
        ], $status, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function error($error, $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __($error),
            'status' => $status,
        ], $status, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
