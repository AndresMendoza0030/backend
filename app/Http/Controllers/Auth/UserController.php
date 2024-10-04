<?php

namespace App\Http\Controllers\Auth;

use App\Http\Middleware\Authenticate;
use App\Http\Requests\ResetUserPasswordRequest;
use App\Http\Requests\SendRecoveryPasswordMailRequest;
use App\Http\Requests\StoreUserRequest;
use App\Mail\PasswordRecoveryMail;
use App\Models\User;
use App\Repositories\Permission\EloquentPermission;
use App\Repositories\User\EloquentUser;
use App\Utils\ApiResponse;
use App\Utils\DatabaseErrorsHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use PDOException;

class UserController extends Controller
{
    private EloquentUser $EloquentUser;
    private EloquentPermission $EloquentPermission;

    public function __construct()
    {
        $this->middleware([Authenticate::class]);
        $this->EloquentUser = new EloquentUser();
        $this->EloquentPermission = new EloquentPermission();
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $password = bcrypt($request->input('password'));

            $data = [
                'name' => $request->input('name'),
                'lastname' => $request->input('lastname'),
                'email' => $request->input('email'),
                'password' => $password
            ];

            return ApiResponse::success($this->EloquentUser->create($data), "Usuario creado exitosamente");

        } catch (PDOException $e) {
            return DatabaseErrorsHandler::handle($e);
        } catch (\Exception $e) {
            return ApiResponse::error(400, $e->getMessage());
        }

    }

    function login(Request $request): JsonResponse
    {
        $data = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $userPermissions = [];
        $userRoles = [];

        try {
            if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                return ApiResponse::error(401, 'Usuario y/o contraseña inválidos');
            }

            $user = Auth::user();

            //Todos los permisos del usuario activos
            $user
                ->permissions()?->
                wherePivot('deleted_at', null)
                ->chunkMap(function ($permission) use (&$userPermissions) {
                    if ($permission->deleted_at == null)
                        $userPermissions[] = ['id' => $permission->permission_id, 'name' => $permission->readable_name];
                });

            $user
                ->roles()?->
                wherePivot('deleted_at', null)
                ->chunkMap(function ($role) use (&$userRoles) {
                    if ($role->deleted_at == null)
                        $userRoles[] = ['id' => $role->role_id, 'name' => $role->name];
                });

            $user?->tokens()->delete();

            return ApiResponse::success
            (
                [
                    'user' => $user,
                    'permissions' => $userPermissions,
                    'roles' => $userRoles,
                    'token' => Auth::user()->createToken('fya')->plainTextToken
                ],
                'Login exitoso'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(400, $e->getMessage());
        }
    }


    function getAllAvailableUsers(Request $request): JsonResponse
    {
        try {
            $params = $request->all();

            $users = $this->EloquentUser->getAllByParams($params);

            $response = ['users' => $users];

            return ApiResponse::success($response, "Usuarios obtenidos exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
            try {
                DB::rollBack();
                return DatabaseErrorsHandler::handle($e);
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }

        } catch (\Exception $e) {
            return ApiResponse::error(400, $e->getMessage());
        }


    }

    function sendRecoveryPasswordMail(SendRecoveryPasswordMailRequest $request)
    {
        try {
            $user = $this->EloquentUser->byEmail($request->email);
            // Crear manualmente el token de restablecimiento de contraseña
            $token = Password::getRepository()->create($user);

            // Enviar el correo con el Mailable
            Mail::to($request->email)->send(new PasswordRecoveryMail($token, $request->email));

            return ApiResponse::success([], 'Correo de recuperación enviado.');


        } catch (\Exception $e) {

            return ApiResponse::error(400, $e->getMessage());
        }
    }
    function resetUserPassword(ResetUserPasswordRequest $request)
    {
        try {

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => bcrypt($password),
                    ])->save();
                }
            );

            if ($status == Password::PASSWORD_RESET) {
                return ApiResponse::success([], 'Contraseña reestablecida exitosamente');
            }

            return ApiResponse::error(400, 'Token no válido o expirado.', []);

        } catch (\Exception $e) {

            return ApiResponse::error(400, $e->getMessage());
        }
    }

    function showResetPasswordForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        return view('auth.reset-password', compact('token', 'email'));
    }

}
