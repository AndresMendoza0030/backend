<?php

namespace App\Http\Controllers\Auth;

use App\Http\Middleware\Authenticate;
use App\Http\Requests\AssignPermissionToUserRequest;
use App\Http\Requests\DeletePermissionRequest;
use App\Http\Requests\RevokePermissionToUserRequest;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\User;
use App\Repositories\Permission\EloquentPermission;
use App\Repositories\Permission\EloquentRole;
use App\Repositories\User\EloquentUser;
use App\Utils\ApiResponse;
use App\Utils\DatabaseErrorsHandler;
use App\Utils\Misc;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Asegúrate de incluir esto al principio de tu archivo
use PDOException;

class PermissionController extends Controller
{
    private EloquentUser $EloquentUser;
    private EloquentPermission $EloquentPermission;


    public function __construct()
    {
        $this->middleware([Authenticate::class]);

        $this->EloquentPermission = new EloquentPermission();
        $this->EloquentUser = new EloquentUser();
    }
    // Método para obtener todos los permisos
    function getAllPermissions(Request $request): JsonResponse
    {
        try {
            $params = $request->all();

            $permissions = $this->EloquentPermission->getAllByParams($params);

            $response = ['permissions' => $permissions];

            return ApiResponse::success($response, "Permisos obtenidos exitosamente");

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


    public function store(StorePermissionRequest $request)
    {
        try {
            $nameWithPattern = Misc::transformToPattern($request->input('name'));
            $deletedPermission = $this->EloquentPermission->getDeletedModel($nameWithPattern);
    
            // Verificar que el arreglo no esté vacío antes de acceder al índice
            if ($deletedPermission && $deletedPermission->isNotEmpty()) {
                $deletedPermission = $deletedPermission[0];
                $this->EloquentPermission->restoreDeletedModel($deletedPermission);
    
                return ApiResponse::success($deletedPermission, 'Permiso restaurado exitosamente');
            }
    
            $data = ['name' => $nameWithPattern];
    
            return $this->EloquentPermission->create($data);
    
        } catch (PDOException $e) {
            return DatabaseErrorsHandler::handle($e);
        } catch (\Exception $e) {
            return ApiResponse::error(400, $e->getMessage());
        }
    }
    
    public function update(UpdatePermissionRequest $request)
    {
        try {

            $data = [
                'id' => $request->input('id'),
                'name' => Misc::transformToPattern($request->input('name')),
            ];

            $permission = $this->EloquentPermission->byId($data['id']);

            $updatedPermission = $this->EloquentPermission->update($data, $permission)->toArray();

            $updatedPermission['name'] = $permission->readable_name;


            return ApiResponse::success($updatedPermission, 'Permiso modificado exitosamente');

        } catch (ModelNotFoundException $e) {
            return DatabaseErrorsHandler::handle($e, 'Permiso no encontrado');
        } catch (PDOException $e) {
            return DatabaseErrorsHandler::handle($e);
        } catch (\Exception $e) {
            return ApiResponse::error(400, $e->getMessage());
        }

    }

    public function delete(DeletePermissionRequest $request)
    {
        try {

            $data = ['id' => $request->input('id')];

            $permission = $this->EloquentPermission->byId($data['id']);

            $updatedPermission = $this->EloquentPermission->delete($permission)->toArray();

            $updatedPermission['name'] = $permission->readable_name;

            return ApiResponse::success($updatedPermission, 'Permiso eliminado exitosamente');

        } catch (ModelNotFoundException $e) {
            return DatabaseErrorsHandler::handle($e, 'Permiso no encontrado');
        } catch (PDOException $e) {
            return DatabaseErrorsHandler::handle($e);
        } catch (\Exception $e) {
            return ApiResponse::error(400, $e->getMessage());
        }

    }

    function assignPermissionToUser(AssignPermissionToUserRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = [
                'user_id' => $request->input('user_id'),
                'permission_id' => $request->input('permission_id'),
            ];

            $userPermissions = [];

            $user = $this->EloquentUser->byId($data['user_id']);
            $permission = $this->EloquentPermission->byId($data['permission_id']);

            $deletedPermission = $this->EloquentPermission->getDeletedUserPermission($user, $permission->id);

            if ($deletedPermission->isNotEmpty()) {
                $this->EloquentPermission->restoreDeletedUserPermission($user, $deletedPermission[0]->id);

                DB::commit();

                $user->permissions()?->wherePivot('deleted_at', null)->chunkMap(function ($userPermission) use (&$userPermissions) {
                    $userPermissions[] = ['id' => $userPermission->permission_id, 'name' => $userPermission->readable_name];
                });

                $response = ['user' => $user, 'permissions' => $userPermissions];

                return ApiResponse::success($response, "Permiso restaurado al usuario creado exitosamente");
            }


            $this->EloquentPermission->assignPermissionToUser($user, $permission);

            DB::commit();

            $user->permissions()?->wherePivot('deleted_at', null)->chunkMap(function ($userPermission) use (&$userPermissions) {
                $userPermissions[] = ['id' => $userPermission->permission_id, 'name' => $userPermission->readable_name];
            });

            $response = ['user' => $user, 'permissions' => $userPermissions];



            return ApiResponse::success($response, "Permiso asignado al usuario exitosamente");

        } catch (ModelNotFoundException $e) {
            try {
                DB::rollBack();
                return DatabaseErrorsHandler::handle($e, 'Usuario o permiso no encontrado');
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }

        } catch (PDOException $e) {
            try {
                DB::rollBack();
                return DatabaseErrorsHandler::handle($e);
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }
        } catch (\Exception $e) {
            try {
                DB::rollBack();
                return ApiResponse::error(400, $e->getMessage());
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }
        } catch (\Throwable $e) {
            try {
                DB::rollBack();
                return ApiResponse::error(400, $e->getMessage());
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }

        }
    }

    function revokePermissionToUser(RevokePermissionToUserRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = [
                'user_id' => $request->input('user_id'),
                'permission_id' => $request->input('permission_id'),
            ];

            $userPermissions = [];

            $user = $this->EloquentUser->byId($data['user_id']);
            $permission = $this->EloquentPermission->byId($data['permission_id']);

            $this->EloquentPermission->revoquePermissionToUser($user, $permission);

            DB::commit();

            return ApiResponse::success([], "Permiso revocado al usuario exitosamente");

        } catch (ModelNotFoundException $e) {
            try {
                DB::rollBack();
                return DatabaseErrorsHandler::handle($e, 'Usuario o permiso no encontrado');
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }

        } catch (PDOException $e) {
            try {
                DB::rollBack();
                return DatabaseErrorsHandler::handle($e);
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }
        } catch (\Exception $e) {
            try {
                DB::rollBack();
                return ApiResponse::error(400, $e->getMessage());
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }
        } catch (\Throwable $e) {
            try {
                DB::rollBack();
                return ApiResponse::error(400, $e->getMessage());
            } catch (\Throwable $e) {
                return DatabaseErrorsHandler::handle($e);
            }

        }
    }

}
