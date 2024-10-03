<?php

namespace App\Repositories;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{

	/**
	 * Search resource by id
	 * @param mixed $id
	 * @return \Illuminate\Database\Eloquent\Model|array
	 */
	public function byId($id);

	/**
	 * Obtener todos los registros con filtros
	 * 
	 * @param $params
	 * @param \Illuminate\Database\Eloquent\Model $model
	 *
	 */
	public function getAllByParams($params);


	/**
	 * Create a new resource
	 * @param array $data
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function create(array $data): Model|array;

	/**
	 * Update an resource
	 * @param array $data
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @return \Illuminate\Database\Eloquent\Model|array
	 */
	public function update(array $data, Model $model);

	/**
	 * Delete an resource
	 * @param Model $model
	 * @return mixed
	 */
	public function delete(Model $model);

	/**
	 * @param $parameter
	 * @return mixed
	 */
	public function getDeletedModel($parameter);

	/**
	 * @param Model $model
	 * @return mixed
	 */
	public function restoreDeletedModel(Model $model);
}
