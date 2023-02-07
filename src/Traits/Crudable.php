<?php

namespace Manowartop\ServiceRepositoryPattern\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait Crudable
 * @package Manowartop\ServiceRepositoryPattern\Traits
 */
trait Crudable
{
    /**
     * Create model with data
     *
     * @param array $data
     * @return Model|null
     */
    public function create(array $data): ?Model
    {
        /** @var Model $model */
        $model = resolve($this->modelClass);

        if (!$model->fill($data)->save()) {
            return null;
        }

        $model->refresh();

        return $model;
    }

    /**
     * Insert records
     *
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        return $this->getQuery()->insert($data);
    }

    /**
     * Update model
     *
     * @param Model|mixed $keyOrModel
     * @param array $data
     * @return Model|null
     */
    public function update($keyOrModel, array $data): ?Model
    {
        $model = $this->resolveModel($keyOrModel);

        if (!$model->update($data)) {
            return null;
        }

        return $model->refresh();
    }

    /**
     * Update or create model
     *
     * @param array $attributes
     * @param array $data
     * @return Model|null
     */
    public function updateOrCreate(array $attributes, array $data): ?Model
    {
        return $this->getQuery()->updateOrCreate($attributes, $data);
    }

    /**
     * Delete model
     *
     * @param Model|mixed $keyOrModel
     * @return bool
     * @throws Exception
     */
    public function delete($keyOrModel): bool
    {
        return !is_null($this->resolveModel($keyOrModel)->delete());
    }
}
