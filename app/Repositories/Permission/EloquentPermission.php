<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use App\Models\User;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
class EloquentPermission implements RepositoryInterface
{


    /**
     * @inheritDoc
     */
    public function byId($id): Model
    {
        return Permission::findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): array
    {
        $permission = Permission::create($data);


        return [
            'id' => $permission->id,
            'name' => $permission->readable_name,
            'created_at' => $permission->created_at,
            'updated_at' => $permission->updated_at,
        ];

    }

    /**
     * @inheritDoc
     */
    public function delete(Model $model)
    {
        $model->delete();
        return $model;
    }

    /**
     * @inheritDoc
     */
    public function update(array $data, Model $model)
    {
        $model->fill($data);

        $model->save();

        return $model;

    }

    public function getDeletedModel($parameter)
    {
        return Permission::withTrashed()->where(['name' => $parameter])->get();
    }

    public function restoreDeletedModel(Model $model)
    {
        return $model->restore();
    }

    public function assignPermissionToUser(User $user, Permission $permission)
    {
        $user->permissions()->save($permission, ['created_at' => now(), 'updated_at' => now()]);
    }

    public function getDeletedUserPermission(User $user, $permissionId)
    {
        return $user
            ->permissions()
            ->wherePivot('deleted_at', '!=', null)
            ->get()
            ->filter(function ($permission) use ($permissionId) {
                return $permission->id == $permissionId;
            });
    }

    public function restoreDeletedUserPermission(User $user, $permissionId)
    {
        $user->permissions()->updateExistingPivot($permissionId, ['deleted_at' => null]);
    }

    public function revoquePermissionToUser(User $user, Permission $permission)
    {
        $user->permissions()->updateExistingPivot($permission->id, ['deleted_at' => now()]);
    }
    public function getAll(): Collection
    {
        return Permission::all();
    }

    public function getAllByParams($params)
    {
        // Paso 1: Obtener permisos según los parámetros filtrados
        $query = Permission::query();

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, 'like', "%$value%");
            }
        }

        // Paso 3: Retornar los resultados finales con solo los campos necesarios
        return $query->get();
    }
}
