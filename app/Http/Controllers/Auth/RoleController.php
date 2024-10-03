<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\AssignPermissionToRoleRequest;
use App\Http\Requests\AssignRoleToUserRequest;
use App\Http\Requests\RevokePermissionToRoleRequest;
use App\Http\Requests\RevokeRoleToUserRequest;
use App\Repositories\Permission\EloquentPermission;
use App\Repositories\User\EloquentUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Middleware\Authenticate;
use App\Http\Requests\DeleteRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Repositories\Permission\EloquentRole;
use App\Utils\ApiResponse;
use App\Utils\DatabaseErrorsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    private EloquentRole $EloquentRole;
    private EloquentUser $EloquentUser;
    private EloquentPermission $EloquentPermission;

    public function __construct()
    {
        $this->middleware([Authenticate::class]);
        $this->EloquentRole = new EloquentRole();
        $this->EloquentUser = new EloquentUser();
        $this->EloquentPermission = new EloquentPermission();
    }

    function createRole(StoreRoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = ['name' => preg_replace('/\s+/', ' ', ucfirst($request->input('name')))];

            $deletedRole = $this->EloquentRole->getDeletedModel($data['name']);

            if ($deletedRole->isNotEmpty()) {

                $deletedRole = $deletedRole[0];

                $this->EloquentRole->restoreDeletedModel($deletedRole);

                DB::commit();

                return ApiResponse::success($deletedRole, 'Rol restaurado exitosamente');
            }

            $result = $this->EloquentRole->create($data);

            $response = ['role' => $result];

            DB::commit();

            return ApiResponse::success($response, "Rol creado exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
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

    function getAllAvailableRoles(Request $request): JsonResponse
    {
        try {
            $params = $request->all();

            $roles = $this->EloquentRole->getAllByParams($params);

            $response = ['roles' => $roles];

            return ApiResponse::success($response, "Roles obtenidos exitosamente");

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

    function updateRole(UpdateRoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = [
                'id' => $request->input('id'),
                'name' => preg_replace('/\s+/', ' ', ucfirst($request->input('name'))),
            ];

            $role = $this->EloquentRole->byId($data['id']);

            $result = $this->EloquentRole->update($data, $role);

            $response = ['role' => $result];

            DB::commit();

            return ApiResponse::success($response, "Rol actualizado exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
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

    function deleteRole(DeleteRoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = ['id' => $request->input('id')];

            $role = $this->EloquentRole->byId($data['id']);

            $result = $this->EloquentRole->delete($role);

            DB::commit();

            return ApiResponse::success([], "Rol eliminado exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
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

    function assignPermissionToRole(AssignPermissionToRoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = [
                'permission_id' => $request->input('permission_id'),
                'role_id' => $request->input('role_id'),
            ];

            $rolePermissions = [];

            $role = $this->EloquentRole->byId($data['role_id']);
            $permission = $this->EloquentPermission->byId($data['permission_id']);

            $deletedPermission = $this->EloquentRole->getDeletedPermissionRole($data['permission_id'], $role);

            if ($deletedPermission->isNotEmpty()) {
                $this->EloquentRole->restoreDeletedPermissionRole($role, $deletedPermission[0]->id);

                DB::commit();

                $role->permissions()->wherePivot('deleted_at', null)->chunkMap(function ($rolePermission) use (&$rolePermissions) {
                    $rolePermissions[] = ['id' => $rolePermission->role_id, 'name' => $rolePermission->readable_name];
                });

                $response = ['role' => $role, 'role_permissions' => $rolePermissions];

                return ApiResponse::success($response, "Permiso restaurado al usuario creado exitosamente");
            }


            $this->EloquentRole->assignPermissionToRole($permission, $role);

            DB::commit();

            $role->permissions()?->wherePivot('deleted_at', null)->chunkMap(function ($rolePermission) use (&$userRoles) {
                $userRoles[] = ['id' => $rolePermission->role_id, 'name' => $rolePermission->name];
            });

            $role = $role->toArray();

            $role['permissions'] = $userRoles;

            $response = ['role' => $role];

            return ApiResponse::success($response, "Permiso asignado al rol exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
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

    function revokePermissionToRole(RevokePermissionToRoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = [
                'permission_id' => $request->input('permission_id'),
                'role_id' => $request->input('role_id'),
            ];

            $role = $this->EloquentRole->byId($data['role_id']);
            $permission = $this->EloquentPermission->byId($data['permission_id']);

            $this->EloquentRole->revoquePermissionToRole($role, $permission);

            DB::commit();

            return ApiResponse::success([], "Permiso revocado al rol exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
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
    function assignRoleToUser(AssignRoleToUserRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = [
                'user_id' => $request->input('user_id'),
                'role_id' => $request->input('role_id'),
            ];

            $rolePermissions = [];
            $userPermissions = [];

            $role = $this->EloquentRole->byId($data['role_id']);
            $user = $this->EloquentUser->byId($data['user_id']);

            $deletedRoleUser = $this->EloquentRole->getDeletedRoleUser($user->id, $role);

            if ($deletedRoleUser->isNotEmpty())
                $this->EloquentRole->restoreDeletedRoleUser($role, $deletedRoleUser[0]->id);
            else
                $this->EloquentRole->assignRoleToUser($role, $user);

            DB::commit();

            $role->permissions()?->wherePivot('deleted_at', null)->wherePivot('deleted_at', null)->chunkMap(function ($rolePermission) use (&$rolePermissions) {
                $rolePermissions[] = ['id' => $rolePermission->role_id, 'name' => $rolePermission->readable_name];
            });
            $user->permissions()?->wherePivot('deleted_at', null)->wherePivot('deleted_at', null)->chunkMap(function ($userPermission) use (&$userPermissions) {
                $userPermissions[] = ['id' => $userPermission->permission_id, 'name' => $userPermission->readable_name];
            });

            $role = $role->toArray();
            $user = $user->toArray();

            $role['permissions'] = $rolePermissions;
            $user['permissions'] = $userPermissions;

            $user['role'] = $role;

            $response = ['user' => $user];

            return ApiResponse::success($response, "Rol asignado al usuario exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
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
    function revokeRoleToUser(RevokeRoleToUserRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = [
                'user_id' => $request->input('user_id'),
                'role_id' => $request->input('role_id'),
            ];

            $rolePermissions = [];
            $userPermissions = [];
            $userRoles = [];

            $role = $this->EloquentRole->byId($data['role_id']);
            $user = $this->EloquentUser->byId($data['user_id']);

            $deletedRoleUser = $this->EloquentRole->getDeletedRoleUser($user->id, $role);

            if ($deletedRoleUser->isNotEmpty())
                $this->EloquentRole->restoreDeletedRoleUser($role, $deletedRoleUser[0]->id);
            else
                $this->EloquentRole->revoqueRoleToUser($role, $user);

            DB::commit();

            $role->permissions()?->wherePivot('deleted_at', null)->chunkMap(function ($rolePermission) use (&$rolePermissions) {
                $rolePermissions[] = ['id' => $rolePermission->role_id, 'name' => $rolePermission->role_name];
            });
            $user->permissions()?->wherePivot('deleted_at', null)->chunkMap(function ($userPermission) use (&$userPermissions) {
                $userPermissions[] = ['id' => $userPermission->permission_id, 'name' => $userPermission->readable_name];
            });

            $user->roles()?->wherePivot('deleted_at', null)->chunkMap(function ($rol) use (&$userRoles) {
                if (!isset($rol->deleted_at))
                    $userRoles[] = ['id' => $rol->id, 'name' => $rol->name];
            });

            $user = $user->toArray();

            $user['permissions'] = $userPermissions;

            $user['roles'] = $userRoles;

            $response = ['user' => $user];

            return ApiResponse::success($response, "Rol quitado al usuario exitosamente");

        } catch (ModelNotFoundException | PDOException | UniqueConstraintViolationException $e) {
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
