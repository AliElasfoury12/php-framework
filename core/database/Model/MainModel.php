<?php 

namespace core\database\Model;

use core\App;
use core\database\Model\relations\Relations;

class MainModel extends QueryBuilder 
{
    public Relations $relations;

    public function __construct() {
        $this->relations = new Relations;
        parent::__construct();
    }

    public function belongsTo (string $class2, string $foreignKey = '', string $primaryKey = '')
    {
        App::$app->model->relations->belongsTo($class2,$foreignKey,$primaryKey);
        return App::$app->model;
    }

    public function hasMany (string $class2, string $foreignKey = '', string $primaryKey = '')
    {
        App::$app->model->relations->hasMany($class2,$foreignKey,$primaryKey);
        return App::$app->model;
    }
    
    public function manyToMany (string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey)
    {
        App::$app->model->relations->manyToMany($relatedClass, $pivotTable, $pivotKey, $relatedKey);
        return App::$app->model;
    }
}