<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Retorna una respuesta JSON.
     *
     * @param int $code
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    public static function create(int $code, $data = null, string $message = ""): JsonResponse
    {
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data ?? new \stdClass(),
        ];

        return response()->json($response, $code);
    }

    /**
     * Retorna una respuesta exitosa (200).
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    public static function success($data = null, string $message = ""): JsonResponse
    {
        return self::create(200, $data, $message);
    }

    /**
     * Retorna una respuesta de error.
     *
     * @param int $code
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function error(int $code, string $message, $data = null): JsonResponse
    {
        return self::create($code, $data, $message);
    }
}
