<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\ApiResponse;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Verificar el dominio del correo electrónico
            $emailDomain = substr(strrchr($googleUser->email, "@"), 1); // Extrae el dominio del correo

            // if ($emailDomain !== 'feyalegria.com') { // Reemplaza 'tu-dominio.com' por el dominio que quieras verificar
            //     return response()->json(['error' => 'El dominio del correo no está permitido'], 403);
            // }

            // Verificar si el usuario ya existe en la base de datos
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $userLastName = $this->extractUserNameAndLastName($googleUser->name);
                // Crear un nuevo usuario si no existe
                $user = User::create([
                    'name' => $googleUser->name,
                    'lastname' => $userLastName,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(24)),
                ]);
            } else
                $user->google_id = $googleUser->id;

            // Iniciar sesión el usuario
            Auth::login($user, true);

            // Generar un nuevo token de Sanctum
            $token = $user->createToken('authToken')->plainTextToken;

            return ApiResponse::success(['token' => $token], 'Successful login');

        } catch (\Exception $e) {

            return response()->json(['error' => 'Error al autenticar con Google'], 500);
        }
    }
    private function extractUserNameAndLastName($fullName)
    {
        // Divide el nombre completo en partes utilizando el espacio como delimitador
        $nameParts = explode(' ', trim($fullName));

        $lastname = '';

        if (count($nameParts) > 1) {
            // Si hay más de una parte, asume que el último elemento es el segundo apellido
            // y el penúltimo es el primer apellido.
            $lastname = $nameParts[count($nameParts) - 2] . ' ' . $nameParts[count($nameParts) - 1];
        } elseif (count($nameParts) === 1) {
            // Si solo hay un nombre, lo tratamos como el apellido (en caso de que Google haya dado solo un apellido).
            $lastname = $nameParts[0];
        }

        return $lastname;
    }
}
