# Laravel Service-Repository Pattern package

This package provides base repository and service for your Laravel Service-Repository pattern: 

  - Adds a repository layer, so you can avoid using your models directly and do all the calls via repository  
  - Adds a service layer which supports CRUD and search functionality 
  
# Usage

  - Create model (f.e Post)
  - Create a repository `PostRepository extends Manowartop\BaseRepositoryAndService\Repositories\BaseRepository`
  - Define property `protected $modelClass = Post::class;` in PostRepository
  - Create a service `PostService extends Manowartop\BaseRepositoryAndService\Services\BaseCrudService`
  - Define property in `protected $repository = PostRepository::class;` in `PostService`

That`s all. Now you have access to the next set of methods:

## Repository methods

### CRUD
  - `getModel(): Model` - get the model of current repository
  - `create(array $data): ?Model` - creates a new model
  - `createMany(array $data): Illuminate\Support\Collection` - create many models from an array
  - `update($keyOrModel, array $data): ?Model` - update model by PK or model instance
  - `updateOrCreate(array $attributes, array $data): ?Model` - update or create model
  - `delete($keyOrModel): bool` - delete model by PK or instance
  - `deleteMany(array $keysOrModels): void` - delete many (array of PK or models)

### Query
  - `find($key): ?Model` - find model by PK
  - `findOrFail($value, ?string $column = null): Model` - find or fail by PK
  - `getAll(array $search = []): Collection` - search a collection of models 
  - `getAllPaginated(array $search = [], int $pageSize = 15): LengthAwarePaginator` - search for paginated models collection
  - `findMany(array $attributes): Collection` - find all models by params
  - `findFirst(array $attributes): ?Model` - find first model by params
  - `with(array $with): BaseRepositoryInterface` - set param `$with` to specify relations to query models with
  - `withCount(array $withCount): BaseRepositoryInterface` - set param `$withCount` to specify relations_count to query models with
 
 If you need to specify query filtering - just override `protected function getFilteredQuery(array $search = []): Builder` in you repository

## Service methods

 - `getAllPaginated(array $search = [], int $pageSize = 15): LengthAwarePaginator` - search for paginated models collection
 - `getAll(array $search = []): Collection` - search a collection of models 
 - `findOrFail($value, ?string $column = null): Model` - find or fail by PK
 - `create(array $data): ?Model` - creates a new model
 - `createMany(array $data): Illuminate\Support\Collection` - create many models from an array
 - `update($keyOrModel, array $data): ?Model` - update model by PK or model instance
 - `updateOrCreate(array $attributes, array $data): ?Model` - update or create model
 - `delete($keyOrModel): bool` - delete model by PK or instance
 - `deleteMany(array $keysOrModels): void` - delete many (array of PK or models)
