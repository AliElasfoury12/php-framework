<?php 

namespace core\database\Model;

use core\App;
use core\base\_Array;
use core\database\Model\Relations\BelongsTo;
use core\database\Model\Relations\HasMany;
use core\database\Model\Relations\ManyToMany;
use core\database\Model\Relations\Relations;

class MainModel extends QueryBuilder 
{
    public _Array $data;
    public string $ids;
    public string $PrimaryKey;
    public Relations $relations;
    public string $table = '';

    public function __construct() {
        $this->relations = new Relations;
        parent::__construct();
    }

    public function belongsTo (string $class2, string $foreignKey = '', string $primaryKey = ''): BelongsTo
    {
        App::$app->model->relations->belongsTo(static::class,$class2,$foreignKey,$primaryKey);
        return App::$app->model->relations->BelongsTo;
    }

    public function hasMany (string $class2, string $foreignKey = '', string $primaryKey = ''): HasMany
    {
        App::$app->model->relations->hasMany(static::class,$class2,$foreignKey,$primaryKey);
        return App::$app->model->relations->HasMany;
    }
    
    public function manyToMany (string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey): ManyToMany
    {
        App::$app->model->relations->manyToMany(static::class, $relatedClass, $pivotTable, $pivotKey, $relatedKey);
        return App::$app->model->relations->ManyToMany;
    }
}