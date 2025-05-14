<?php 

namespace core\Database\Model;

use core\App;
use core\base\_Array;
use core\Database\Model\Query\QueryBuilder;
use core\Database\Model\Relations\BelongsTo;
use core\Database\Model\Relations\HasMany;
use core\Database\Model\Relations\ManyToMany;
use core\Database\Model\Relations\Relations;

class MainModel extends QueryBuilder 
{
    public _Array $data;
    public string $ids;
    public string $PrimaryKey;
    public Relations $relations;
    public string $table;
    public string $class;

    public function __construct() {
        $this->relations = new Relations;
        parent::__construct();
    }

    public function belongsTo (string $class2, string $foreignKey = '', string $primaryKey = ''): BelongsTo
    {
        return App::$app->model->relations->BelongsTo
        ->makeRelation(static::class,$class2,$foreignKey,$primaryKey);
    }

    public function hasMany (string $class2, string $foreignKey = '', string $primaryKey = ''): HasMany
    {
        return App::$app->model->relations->HasMany
        ->makeRelation(static::class,$class2,$foreignKey,$primaryKey);
    }
    
    public function manyToMany (string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey): ManyToMany
    {
        return App::$app->model->relations->ManyToMany
        ->makeRelation(static::class, $relatedClass, $pivotTable, $pivotKey, $relatedKey);
    }
}