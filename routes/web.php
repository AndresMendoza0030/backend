<?php

use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::get('password-reset-form', [UserController::class, 'showResetPasswordForm']); // Para mostrar el formulario
Route::get('/test-db', function () {
    try {
        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        return 'Conectado a la base de datos: ' . $dbName;
    } catch (\Exception $e) {
        return 'Error al conectar a la base de datos: ' . $e->getMessage();
    }
});
