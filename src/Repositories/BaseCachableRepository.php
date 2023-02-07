<?php

namespace Manowartop\ServiceRepositoryPattern\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Manowartop\ServiceRepositoryPattern\Exceptions\Repository\InvalidModelClassException;
use Manowartop\ServiceRepositoryPattern\Exceptions\Repository\RepositoryException;
use Manowartop\ServiceRepositoryPattern\Exceptions\Repository\WrongSearchParametersException;
use Manowartop\ServiceRepositoryPattern\Repositories\Contracts\BaseCachableRepositoryInterface;

/**
 * Class BaseCachableRepository
 * @package Manowartop\ServiceRepositoryPattern\Repositories
 */
class BaseCachableRepository extends BaseRepository implements BaseCachableRepositoryInterface
{
    /**
     * Cache ttl in minutes
     *
     * @var integer
     */
    protected $cacheTtl = 60;

    /**
     * @var array
     */
    protected $cacheTags = [];

    /**
     * BaseCachableRepository constructor.
     * @throws RepositoryException
     * @throws InvalidModelClassException
     */
    public function __construct()
    {
        parent::__construct();

        $this->cacheTags = $this->getCacheTags();

        if (empty($this->cacheTags)) {
            throw new RepositoryException('Cache tags could not be empty');
        }
    }

    /**
     * @param array $search
     * @return Collection
     */
    public function getAll(array $search = []): Collection
    {
        return Cache::tags($this->cacheTags)->remember(
            $this->getCacheKeyFromParams($search, 'all'),
            $this->getTtl(),
            function () use ($search) {
                return parent::getAll($search);
            }
        );
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
        return Cache::tags($this->cacheTags)->remember(
            $this->getCacheKeyFromParams($search, 'paginated'),
            $this->getTtl(),
            function () use ($search, $pageSize) {
                return parent::getAllPaginated($search, $pageSize);
            }
        );
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
        return Cache::tags($this->cacheTags)->remember(
            $this->getCacheKeyFromParams($attributes, 'first'),
            $this->getTtl(),
            function () use ($attributes) {
                return parent::findFirst($attributes);
            }
        );
    }

    /**
     * Find model by PK
     *
     * @param $key
     * @return Model|null
     */
    public function find($key): ?Model
    {
        return Cache::tags($this->cacheTags)->remember(
            $this->getCacheKeyFromParams([], $key),
            $this->getTtl(),
            function () use ($key) {
                return parent::find($key);
            }
        );
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
        return Cache::tags($this->cacheTags)->remember(
            $this->getCacheKeyFromParams(
                is_null($column) ? [] : [$column => $value],
                $value
            ),
            $this->getTtl(),
            function () use ($value, $column) {
                return parent::findOrFail($value, $column);
            }
        );
    }

    /**
     * Create model with data
     *
     * @param array $data
     * @return Model|null
     */
    public function create(array $data): ?Model
    {
        $model = parent::create($data);

        Cache::tags($this->cacheTags)->flush();
        $this->cacheModel($model);

        return $model;
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
        $model = parent::update($keyOrModel, $data);

        Cache::tags($this->cacheTags)->flush();

        $this->cacheModel($model);

        return $model;
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
        $model = parent::updateOrCreate($attributes, $data);

        Cache::tags($this->cacheTags)->flush();

        $this->cacheModel($model);

        return $model;
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
        $model = !$keyOrModel instanceof Model
            ? $this->findOrFail($keyOrModel)
            : $keyOrModel;

        Cache::tags($this->cacheTags)->flush();

        return !is_null($model->delete());
    }

    /**
     * Get cache key from query params
     *
     * @param array $params
     *
     * @param string $methodNameKey
     * @return string
     */
    protected function getCacheKeyFromParams(array $params = [], string $methodNameKey = 'default'): string
    {
        ksort($params);
        return $methodNameKey . '.' . (implode(
                '|',
                array_map(function ($key, $value) {
                    if (!is_array($value)) {
                        return "$key=$value";
                    }

                    return "$value[0]=" . ($value[2] ?? $value[1]);
                }, array_keys($params), array_values($params))
            ));
    }

    /**
     * Get cache tags based on model name
     *
     * @return array
     */
    protected function getCacheTags(): array
    {
        return [Str::camel(last(explode('\\', $this->modelClass)))];
    }

    /**
     * Cache model
     *
     * @param null|string|integer|Model $keyOrModel
     * @return void
     */
    protected function cacheModel($keyOrModel = null): void
    {
        if (is_null($keyOrModel)) {
            return;
        }

        $model = !$keyOrModel instanceof Model
            ? $this->findOrFail($keyOrModel)
            : $keyOrModel;

        Cache::tags($this->cacheTags)->put(
            $this->getCacheKeyFromParams([], $model->getKey()),
            $model,
            $this->getTtl()
        );
    }

    /**
     * Get cache ttl in seconds
     *
     * @return int
     */
    private function getTtl(): int
    {
        return $this->cacheTtl * 60;
    }
}
