<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class EloquentRole implements RepositoryInterface
{

    public function byId($id)
    {
        return Role::findOrFail($id);
    }

    public function create(array $data): Model|array
    {
        return Role::create($data);
    }

    public function update(array $data, Model $model)
    {
        $model->fill($data);

        $model->save();

        return $model;
    }

    public function delete(Model $model)
    {
        $model->delete();
        return $model;
    }

    public function getDeletedModel($parameter)
    {
        return Role::withTrashed()->where(['name' => $parameter])->get();

    }

    public function restoreDeletedModel(Model $model)
    {
        return $model->restore();
    }
    /**
     * @inheritDoc
     */
    public function getAllByParams($params)
    {
        // Paso 1: Obtener roles según los parámetros filtrados
        $query = Role::query();

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, 'like', "%$value%");
            }
        }
        // Paso 2: Procesar permisos y construir el resultado
        $finalResults = [];

        $query->chunkMap(function ($role) use (&$finalResults) {
            // Inicializar el array de permisos filtrados
            $rolePermissions = [];

            // Obtener permisos con soft deletes excluidos
            $role
                ->permissions()
                ->wherePivot('deleted_at', null)
                ->chunkMap(function ($permission) use (&$rolePermissions) {
                    $rolePermissions[] = [
                        'id' => $permission->id,
                        'name' => $permission->readable_name,
                    ];
                });

            // Convertir el rol a array y agregar permisos filtrados
            $roleArray = $role->toArray();
            $roleArray['permissions'] = $rolePermissions;

            // Agregar el rol procesado al resultado final
            $finalResults[] = $roleArray;
        });

        // Paso 3: Retornar los resultados finales con solo los campos necesarios
        return $finalResults;
    }
    public function assignPermissionToRole($permission, $role)
    {
        $role->permissions()->save($permission, ['created_at' => now(), 'updated_at' => now()]);
    }
    public function revoquePermissionToRole(Role $role, Permission $permission)
    {
        $role->permissions()->updateExistingPivot($permission->id, ['deleted_at' => now()]);
    }
    public function getDeletedPermissionRole($permissionId, $role)
    {

        return $role
            ->permissions()
            ->wherePivot('deleted_at', '!=', null)
            ->get()
            ->filter(function ($permission) use ($permissionId) {
                return $permission->id == $permissionId;
            });
    }
    public function restoreDeletedPermissionRole($role, $permissionId)
    {
        $role->permissions()->updateExistingPivot($permissionId, ['deleted_at' => null]);
    }
    public function assignRoleToUser($role, $user)
    {
        $user->roles()->save($role, ['created_at' => now(), 'updated_at' => now()]);
    }
    public function revoqueRoleToUser(Role $role, User $user)
    {
        $role->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);
    }
    public function getDeletedRoleUser($userId, $role)
    {

        return $role
            ->users()
            ->wherePivot('deleted_at', '!=', null)
            ->get()
            ->filter(function ($user) use ($userId) {
                return $user->id == $userId;
            });
    }
    public function restoreDeletedRoleUser($role, $userId)
    {
        $role->users()->updateExistingPivot($userId, ['deleted_at' => null]);
    }
}
