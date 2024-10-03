<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;

class DatabaseErrorsHandler
{
    static function handle(\Exception $error, $message = 'Ha ocurrido un error a nivel de base de datos'): JsonResponse
    {
        $errorCode = $error->getCode();
        $response = null;

        match ($errorCode) {
            '23000' => $response = ApiResponse::error(400, 'Se está intentando ingresar un registro duplicado', ['code' => $errorCode, 'message' => $error->getMessage()]),
            default => $response = ApiResponse::error(400, $message.': '.$error->getMessage(), ['code' => $errorCode == 0 ? 400 : $errorCode]),
        };

        return $response;
    }
}
