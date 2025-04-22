<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;

class Relations {

    public _Array $relations;
    public _Array $withCount_relations;
    public ?CurrentRelation $currentRelation = null;
    public ?RELATIONSTYPE $Types = null;
    public BelongsTo $BelongsTo;
    public ManyToMany $ManyToMany;
    public HasMany $HasMany;
    public EagerLoading $eagerLoading;

    public function __construct() 
    {
        $this->relations = new _Array;
        $this->withCount_relations = new _Array;
        $this->currentRelation = new CurrentRelation;
        $this->Types = new RELATIONSTYPE;
        $this->BelongsTo = new BelongsTo;
        $this->ManyToMany = new ManyToMany;
        $this->HasMany = new HasMany;
        $this->eagerLoading = new EagerLoading;
    }

    protected function hasOne (string $class1, string $class2, string $foreignKey = '', string $primaryKey = ''): static
    {
        $model = App::$app->model;
        $this->tablesAndKeys($class1,$class2, $foreignKey, $primaryKey,$model->relations->Types::HASONE);
        return new static;
    }

    public function belongsTo (string $class1, string $class2, string $foreignKey, string $primaryKey): void
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $table1 = $model->getClassTable($class1);
        $table2 = $model->getClassTable($class2);

        $primaryKey = $primaryKey?: $db->getPK($table1);
        $foreignKey = $foreignKey ?: $db->getFK($table1, $table2);
       
        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->Types::BELONGSTO;
        $currentRelation->table1 = $table1;
        $currentRelation->table2 = $table2;
        $currentRelation->FK1 = $foreignKey;
        $currentRelation->PK2 = $primaryKey;
        $currentRelation->model1 = $class1;
        $currentRelation->model2 = $class2;
    }

    public function hasMany (string $class1, string $class2, string $foreignKey = '', string $primaryKey = ''): static 
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $db = App::$app->db;

        $table1 = $model->getClassTable($class1);
        $table2 = $model->getClassTable($class2);

        $primaryKey = $primaryKey?: $db->getPK($table1);
        $foreignKey = $foreignKey ?: $db->getFK($table2, $table1) ;

        $currentRelation->type = $model->relations->Types::HASMANY;
        $currentRelation->table1 = $table1;
        $currentRelation->table2 = $table2;
        $currentRelation->FK2 = $foreignKey;
        $currentRelation->PK1 = $primaryKey;
        $currentRelation->model1 = $class1;
        $currentRelation->model2 = $class2;
        return new static;
    }

    public function manyToMany (string $baseClass, string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey): void 
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->Types::MANYTOMANY;
        $currentRelation->table1 = $model->getClassTable($baseClass);
        $currentRelation->table2 = $model->getClassTable($relatedClass);
        $currentRelation->PK1 = $db->getPK($currentRelation->table1);
        $currentRelation->PK2 = $db->getPK($currentRelation->table2);
        $currentRelation->pivotTable = $pivotTable;
        $currentRelation->pivotKey = $pivotKey;
        $currentRelation->relatedKey = $relatedKey;
        $currentRelation->model1 = $baseClass;
        $currentRelation->model2 = $relatedClass;

    }

    private function tablesAndKeys (string $class1, string $class2, string $primaryKey, string $foreignKey, string $relation): void
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $db = App::$app->db;

        $table1 = $model->getClassTable($class1);
        $table2 = $model->getClassTable($class2);

        $primaryKey = $primaryKey?: $db->getPK($table1);
        $foreignKey = $foreignKey ?: $db->getFK($table2, $table1) ;

        $currentRelation->type = $relation;
        $currentRelation->table1 = $table1;
        $currentRelation->table2 = $table2;
        $currentRelation->FK1 = $foreignKey;
        $currentRelation->PK2 = $primaryKey;
        $currentRelation->model1 = $class1;
        $currentRelation->model2 = $class2;
    }

}