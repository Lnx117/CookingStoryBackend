<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Успешный ответ.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        string $message = 'Успешно',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $code);
    }

    /**
     * Ответ с ошибкой.
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function error(
        string $message = 'Ошибка',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'status' => false,
            'code' => $code,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }
}