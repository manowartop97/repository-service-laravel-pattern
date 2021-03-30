<?php

namespace Manowartop\ServiceRepositoryPattern\Services;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Manowartop\ServiceRepositoryPattern\Exceptions\Service\ServiceException;
use Manowartop\ServiceRepositoryPattern\Repositories\Contracts\BaseRepositoryInterface;
use Manowartop\ServiceRepositoryPattern\Services\Contracts\BaseCrudServiceInterface;

/**
 * Class BaseCrudService
 * @package Manowartop\ServiceRepositoryPattern\Services
 */
abstract class BaseCrudService implements BaseCrudServiceInterface
{
    /**
     * @var BaseRepositoryInterface
     */
    protected $repository;

    /**
     * BaseCrudService constructor.
     */
    public function __construct()
    {
        $this->repository = resolve($this->repository);
    }

    /**
     * Get filtered results
     *
     * @param array $search
     * @param int $pageSize
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(array $search = [], int $pageSize = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($search, $pageSize);
    }

    /**
     * Get all records as collection
     *
     * @param array $search
     * @return EloquentCollection
     */
    public function getAll(array $search = []): EloquentCollection
    {
        return $this->repository->getAll($search);
    }

    /**
     * Find or fail the model
     *
     * @param $key
     * @return Model
     */
    public function findOrFail($key): Model
    {
        return $this->repository->findOrFail($key);
    }

    /**
     * Create model
     *
     * @param array $data
     * @return Model|null
     * @throws ServiceException
     */
    public function create(array $data): ?Model
    {
        if (is_null($model = $this->repository->create($data))) {
            throw new ServiceException('Error while creating model');
        }

        return $model;
    }

    /**
     * Create many models
     *
     * @param array $attributes
     * @return Collection
     * @throws ServiceException
     */
    public function createMany(array $attributes): Collection
    {
        $models = $this->repository->createMany($attributes);

        if ($models->isEmpty()) {
            throw new ServiceException('Error while creating multiple models');
        }

        return $models;
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
        if (is_null($model = $this->repository->updateOrCreate($attributes, $data))) {
            throw new ServiceException('Error while creating or updating the model');
        }

        return $model;
    }

    /**
     * Update model
     *
     * @param $keyOrModel
     * @param array $data
     * @return Model|null
     */
    public function update($keyOrModel, array $data): ?Model
    {
        return $this->repository->update($keyOrModel, $data);
    }

    /**
     * Delete model
     *
     * @param $keyOrModel
     * @return bool
     * @throws Exception
     */
    public function delete($keyOrModel): bool
    {
        if (!$this->repository->delete($keyOrModel)) {
            throw new ServiceException('Error while deleting model');
        }

        return true;
    }

    /**
     * Delete many records
     *
     * @param array $keysOrModels
     * @return void
     */
    public function deleteMany(array $keysOrModels): void
    {
        $this->repository->deleteMany($keysOrModels);
    }
}
