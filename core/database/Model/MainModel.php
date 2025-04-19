<?php 

namespace core\database\Model;

use core\App;
use core\database\Model\relations\BelongsTo;
use core\database\Model\relations\HasMany;
use Core\Database\Model\Relations\ManyToMany;
use core\database\Model\relations\Relations;

class MainModel extends QueryBuilder 
{
    public Relations $relations;
    public string $ids;
    public string $PrimaryKey;
    public string $table = '';


    public function __construct() {
        $this->relations = new Relations;
        parent::__construct();
    }

    public function belongsTo (string $class2, string $foreignKey = '', string $primaryKey = ''): BelongsTo
    {
        App::$app->model->relations->belongsTo($class2,$foreignKey,$primaryKey);
        return App::$app->model->relations->BelongsTo;
    }

    public function hasMany (string $class2, string $foreignKey = '', string $primaryKey = ''): HasMany
    {
        App::$app->model->relations->hasMany($class2,$foreignKey,$primaryKey);
        return App::$app->model->relations->HasMany;
    }
    
    public function manyToMany (string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey): ManyToMany
    {
        App::$app->model->relations->manyToMany($relatedClass, $pivotTable, $pivotKey, $relatedKey);
        return App::$app->model->relations->ManyToMany;
    }
}