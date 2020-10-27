<?php

namespace Manowartop\ServiceRepositoryPattern\Traits;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data) {
            /** @var Model $model */
            $model = resolve($this->modelClass);

            if (!$model->fill($data)->save()) {
                return null;
            }

            $model->refresh();

            return $model;
        });
    }

    /**
     * Create many
     *
     * @param array $attributes
     * @return Collection
     */
    public function createMany(array $attributes): SupportCollection
    {
        return DB::transaction(function () use ($attributes) {
            $models = collect();

            foreach ($attributes as $data) {
                $models->push($this->create($data));
            }

            return $models;
        });
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
        return DB::transaction(function () use ($keyOrModel, $data) {

            $model = !$keyOrModel instanceof Model
                ? $this->findOrFail($keyOrModel)
                : $keyOrModel;

            if (!$model->fill($data)->save()) {
                return null;
            }

            $model->refresh();
            return $model;
        });
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
        return DB::transaction(function () use ($keyOrModel) {
            $model = !$keyOrModel instanceof Model
                ? $this->findOrFail($keyOrModel)
                : $keyOrModel;

            return !is_null($model->delete());
        });
    }

    /**
     * Delete many models
     *
     * @param array $keysOrModels
     * @return void
     */
    public function deleteMany(array $keysOrModels): void
    {
        DB::transaction(function () use ($keysOrModels) {
            foreach ($keysOrModels as $keyOrModel) {
                $this->delete($keyOrModel);
            }
        });
    }
}
