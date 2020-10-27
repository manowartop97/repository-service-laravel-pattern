<?php

namespace Manowartop\ServiceRepositoryPattern\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Manowartop\ServiceRepositoryPattern\Exceptions\Repository\WrongSearchParametersException;
use Manowartop\ServiceRepositoryPattern\Models\BaseModel;
use Manowartop\ServiceRepositoryPattern\Repositories\Contracts\BaseRepositoryInterface;

/**
 * Class Queryable
 * @package Manowartop\ServiceRepositoryPattern\Traits
 *
 * @property Model|BaseModel $model
 */
trait Queryable
{
    /**
     * Array of "with" relations
     *
     * @var array
     */
    protected $with = [];

    /**
     * Array of "withCount" relations
     *
     * @var array
     */
    protected $withCount = [];

    /**
     * Find model by PK
     *
     * @param $key
     * @return Model|null
     */
    public function find($key): ?Model
    {
        return $this->getQuery()->whereKey($key)->first();
    }

    /**
     * Find or fail by primary key or custom column
     *
     * @param $value
     * @param string|null $column
     * @return Model
     */
    public function findOrFail($value, ?string $column = null): Model
    {
        if (is_null($column)) {
            return $this->getQuery()->findOrFail($value);
        }

        if (is_null($model = $this->getQuery()->where($column, $value)->first())) {
            throw (new ModelNotFoundException)->setModel(get_class($model), $value);
        }

        return $model;
    }

    /**
     * Get filtered collection
     *
     * @param array $search
     * @return Collection
     */
    public function getAll(array $search = []): Collection
    {
        return $this->getFilteredQuery($search)->get();
    }

    /**
     * Get paginated data
     *
     * @param array $search
     * @param int $pageSize
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(array $search = [], int $pageSize = 15): LengthAwarePaginator
    {
        return $this->getFilteredQuery($search)->paginate($pageSize);
    }

    /**
     * Find all models by params
     *
     * @param array $attributes
     * @return Collection
     * @throws WrongSearchParametersException
     */
    public function findMany(array $attributes): Collection
    {
        return $this->filterQueryByAttributes($attributes)->get();
    }

    /**
     * Find first model
     *
     * @param array $attributes
     * @return Model|null
     * @throws WrongSearchParametersException
     */
    public function findFirst(array $attributes): ?Model
    {
        return $this->filterQueryByAttributes($attributes)->first();
    }

    /**
     * Set with
     *
     * @param array $with
     * @return BaseRepositoryInterface
     */
    public function with(array $with): BaseRepositoryInterface
    {
        $this->with = $with;

        return $this;
    }

    /**
     * Set with count
     *
     * @param array $withCount
     * @return BaseRepositoryInterface
     */
    public function withCount(array $withCount): BaseRepositoryInterface
    {
        $this->withCount = $withCount;

        return $this;
    }

    /**
     * Get filtered query
     *
     * @param array $search
     * @return Builder
     */
    protected function getFilteredQuery(array $search = []): Builder
    {
        return $this->getQuery()->orderBy('id', 'desc');
    }

    /**
     * Filter query by attributes
     *
     * @param array $attributes
     * @return Builder
     * @throws WrongSearchParametersException
     */
    protected function filterQueryByAttributes(array $attributes): Builder
    {
        $query = $this->getQuery();

        foreach ($attributes as $attributeData) {

            if (!isset($attributeData[0]) || (!isset($attributeData[1]) && !array_key_exists(1, $attributeData))) {
                throw new WrongSearchParametersException();
            }

            // its just [attribute, value]
            if (count($attributeData) <= 2) {
                $query->where($attributeData[0], $attributeData[1]);
                continue;
            }

            // attributeData[1] - is an search operator
            switch ($attributeData[1]) {
                case '=':
                case 'like':
                case '>':
                case '<':
                case '>=':
                case '<=':
                    if (!isset($attributeData[2])) {
                        throw new WrongSearchParametersException("Search attributes should be like [attribute, operator, value]");
                    }

                    $query->where($attributeData[0], $attributeData[1], ($attributeData[0] === 'like' ? "%{$attributeData[2]}%" : $attributeData[2]));
                    break;
                case 'between':
                    if (!is_array($attributeData[2])) {
                        throw new WrongSearchParametersException('When the operator is `between` the data must be an array');
                    }
                    $query->whereBetween($attributeData[0], $attributeData[2]);
                    break;
                case 'in':
                case 'not in':
                    if (!is_array($attributeData[2])) {
                        throw new WrongSearchParametersException('When the operator is `in` the data must be an array');
                    }
                    $attributeData[1] === 'in'
                        ? $query->whereIn($attributeData[0], $attributeData[2])
                        : $query->whereNotIn($attributeData[0], $attributeData[2]);
                    break;
                case 'is null':
                case 'not null':
                    $attributeData[1] === 'is null'
                        ? $query->whereNull($attributeData[0])
                        : $query->whereNotNull($attributeData[0]);
                    break;
                default:
                    throw new WrongSearchParametersException("Operator '{$attributeData[1]}' is not supported or wrong");
                    break;
            }
        }

        return $query;
    }

    /**
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        $query = $this->model::query();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        if (!empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        return $query;
    }
}
