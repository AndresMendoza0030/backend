<?php

namespace App\Http\Middleware;

use App\Utils\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $routeUri = $request->path();  // Obtener la URI de la ruta

        // Definir rutas que no requieren autenticación usando la URI
        $publicUris = [
            'api/login',
            'api/password-reset',
            'api/password-recovery',
            'password-reset-form'
        ];

        if (in_array($routeUri, $publicUris)) {
            return $next($request);
        }

        // Si $guards está vacío, se establece un valor por defecto para Sanctum
        $guards = empty($guards) ? ['sanctum'] : $guards;


        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            return ApiResponse::error(401, 'No autorizado', $e->getMessage());
        }

        return $next($request);
    }

    protected function authenticate(Request $request, array $guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return;
            }
        }

        throw new AuthenticationException(
            'Usuario sin token o token inválido',
            $guards,
            $this->redirectTo($request)
        );
    }

    protected function redirectTo(Request $request)
    {
        // Define your redirect path or null if you don't need redirection
        return null;
    }
}