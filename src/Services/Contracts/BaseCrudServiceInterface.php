<?php


namespace Manowartop\ServiceRepositoryPattern\Services\Contracts;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Interface BaseCrudServiceInterface
 * @package Manowartop\ServiceRepositoryPattern\Services\Contracts
 */
interface BaseCrudServiceInterface
{
    /**
     * Get filtered results
     *
     * @param array $search
     * @param int $pageSize
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(array $search = [], int $pageSize = 15): LengthAwarePaginator;

    /**
     * Get all records as collection
     *
     * @param array $search
     * @return EloquentCollection
     */
    public function getAll(array $search = []): EloquentCollection;

    /**
     * Find or fail the model
     *
     * @param $key
     * @return Model
     */
    public function findOrFail($key): Model;

    /**
     * Create model
     *
     * @param array $data
     * @return Model|null
     */
    public function create(array $data): ?Model;

    /**
     * Create many models
     *
     * @param array $attributes
     * @return Collection
     */
    public function createMany(array $attributes): Collection;

    /**
     * Update or create model
     *
     * @param array $attributes
     * @param array $data
     * @return Model|null
     */
    public function updateOrCreate(array $attributes, array $data): ?Model;

    /**
     * Update model
     *
     * @param mixed $keyOrModel
     * @param array $data
     * @return Model|null
     */
    public function update($keyOrModel, array $data): ?Model;

    /**
     * Delete model
     *
     * @param mixed $keyOrModel
     * @return bool
     * @throws Exception
     */
    public function delete($keyOrModel): bool;

    /**
     * Delete many records
     *
     * @param array $keysOrModels
     * @return void
     */
    public function deleteMany(array $keysOrModels): void;
}
