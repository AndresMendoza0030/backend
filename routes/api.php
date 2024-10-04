<?php

use App\Http\Controllers\Auth\PermissionController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;



Route::post('/user', [UserController::class, 'store']);
Route::get('/users', [UserController::class, 'getAllAvailableUsers']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/password-recovery', [UserController::class, 'sendRecoveryPasswordMail']);
Route::post('/password-reset', [UserController::class, 'resetUserPassword']);


Route::post('/permission', [PermissionController::class, 'store']);
Route::put('/permission', [PermissionController::class, 'update']);
Route::delete('/permission', [PermissionController::class, 'delete']);
Route::get('/permissions', [PermissionController::class, 'getAllPermissions']);

Route::post('/assign-permission', [PermissionController::class, 'assignPermissionToUser']);
Route::post('/revoke-permission', [PermissionController::class, 'revokePermissionToUser']);

Route::get('/roles', [RoleController::class, 'getAllAvailableRoles']);
Route::post('/roles', [RoleController::class, 'createRole']);
Route::put('/roles', [RoleController::class, 'updateRole']);
Route::delete('/roles', [RoleController::class, 'deleteRole']);
Route::post('/assign-permission-role', [RoleController::class, 'assignPermissionToRole']);
Route::post('/revoke-permission-role', [RoleController::class, 'revokePermissionToRole']);
Route::post('/assign-role-to-user', [RoleController::class, 'assignRoleToUser']);
Route::post('/revoke-role-to-user', [RoleController::class, 'revokeRoleToUser']);

