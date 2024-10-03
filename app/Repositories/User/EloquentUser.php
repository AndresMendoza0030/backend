<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class EloquentUser implements RepositoryInterface
{


    /**
     * @inheritDoc
     */
    public function byId($id)
    {
        return User::findOrFail($id);
    }

    public function byEmail($email)
    {
        return User::where(['email' => $email])->first();
    }

    public function getAll()
    {
        return User::query();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Model
    {
        return User::create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(Model $model)
    {
    }

    /**
     * @inheritDoc
     */
    public function update(array $data, Model $model)
    {
    }

    public function getDeletedModel($parameter)
    {
        $deletedUser = User::withTrashed()->find($parameter);
    }

    public function restoreDeletedModel(Model $model)
    {
        // TODO: Implement restoreDeletedModel() method.
    }

    public function getAllByParams($params)
    {
        // Paso 1: Obtener usuarios según los parámetros filtrados
        $query = User::query();

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, 'like', "%$value%");
            }
        }
        // Paso 2: Procesar usuarios y construir el resultado
        $finalResults = [];

        $query->chunkMap(function ($user) use (&$finalResults) {
            // Inicializar el array de permisos y roles de usuarios filtrados
            $userPermissions = [];
            $userRoles = [];

            // Obtener permisos con soft deletes excluidos
            $user
                ->permissions()?->
                wherePivot('deleted_at', null)
                ->chunkMap(function ($permission) use (&$users, &$userPermissions) {
                    $userPermissions[] = [
                        'id' => $permission->id,
                        'name' => $permission->readable_name,
                    ];
                });

            // Obtener roles con soft deletes excluidos
            $user
                ->roles()?->
                wherePivot('deleted_at', null)
                ->chunkMap(function ($role) use (&$users, &$userRoles) {
                    $userRoles[] = [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                });

            // Convertir el rol a array y agregar permisos filtrados
            $userArray = $user->toArray();
            $userArray['permissions'] = $userPermissions;
            $userArray['roles'] = $userRoles;

            // Agregar el rol procesado al resultado final
            $finalResults[] = $userArray;
        });

        // Paso 3: Retornar los resultados finales con solo los campos necesarios
        return $finalResults;
    }
}
