<?php

namespace Manowartop\ServiceRepositoryPattern\Models;

use Illuminate\Database\Eloquent\Model;
use Manowartop\ServiceRepositoryPattern\Models\Contracts\BaseModelEntityInterface;

/**
 * Class BaseModel
 * @package manowartop\ServiceRepositoryPattern\Models
 */
class BaseModel extends Model implements BaseModelEntityInterface
{
    /**
     * @return string
     */
    public static function getTableName():string
    {
        return (new static())->getTable();
    }
}
