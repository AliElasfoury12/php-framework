<?php 

namespace core\database\Model\relations;

use core\App;

class Relations {

    use WithTrait, WithCountTrait;

    public array $relations = [];
    public string $relationName = '';
    public array $withCount_relations = [];
    public array $relationData = [];
    public string $requestedCoulmns = '*';
    public ?CurrentRelation $currentRelation = null;
    public ?RELATIONSTYPE $relationTypes = null;
    public BelongsTo $BelongsTo;
    public ManyToMany $ManyToMany;
    public HasMany $HasMany;
    public Nested $Nested;

    public function __construct() 
    {
        $this->currentRelation = new CurrentRelation;
        $this->relationTypes = new RELATIONSTYPE;
        $this->BelongsTo = new BelongsTo;
        $this->ManyToMany = new ManyToMany;
        $this->HasMany = new HasMany;
        $this->Nested = new Nested;
    }

    protected function hasOne (string $class2, string $foreignKey = '', string $primaryKey = ''): static
    {
        $model = App::$app->model;
        $this->tablesAndKeys($class2, $foreignKey, $primaryKey,$model->relations->relationTypes::HASONE);
        return new static;
    }

    public function belongsTo (string $class2, string $foreignKey = '', string $primaryKey = ''): static 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;
        $db = App::$app->db;
        $table2 = $model->getClassTable($class2);//users

        $primaryKey = $primaryKey ?: $db->getPK($table2) ;
        $foreignKey = $foreignKey ?: $db->getFK($model->table,$table2) ;

        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->relationTypes::BELONGSTO;
        $currentRelation->table2 = $table2;
        $currentRelation->foreignKey = $foreignKey;
        $currentRelation->primaryKey = $primaryKey;

        return new static;
    }

    public function hasMany (string $class2, string $foreignKey = '', string $primaryKey = ''): static 
    {
        $model = App::$app->model;
        $this->tablesAndKeys($class2, $foreignKey, $primaryKey,$model->relations->relationTypes::HASMANY);
        return new static;
    }

    public function manyToMany (string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey): static 
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $model->relations->relationTypes::MANYTOMANY;
        $currentRelation->table2 = $model->getClassTable($relatedClass);
        $currentRelation->primaryKey = $db->getPK($currentRelation->table2);
        $currentRelation->pivotTable = $pivotTable;
        $currentRelation->pivotKey = $pivotKey;
        $currentRelation->relatedKey = $relatedKey;

        return new static;
    }

    private function tablesAndKeys (string $class2, string $primaryKey, string $foreignKey, string $relation): void
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $table2 = $model->getClassTable($class2);//users

        $primaryKey = $primaryKey?: $model->primaryKey;
        $foreignKey = $foreignKey ?: $db->getFK($table2, $model->table) ;

        $currentRelation = $model->relations->currentRelation;
        $currentRelation->type = $relation;
        $currentRelation->table2 = $table2;
        $currentRelation->foreignKey = $foreignKey;
        $currentRelation->primaryKey = $primaryKey;
    }

    public static function extraQuery (string $table = ''): array 
    {
        $model = App::$app->model;
        $query = '';
        $select = $table ? "$table.*": '*';
        if($model->query) {
            $query = $model->query->getQuery();
            if(isset($model->query->where)){
                $query = str_replace('WHERE', 'AND', $query);
            }

            if(isset($model->query->select)){
                $select = $model->query->handleSelect($table);
            }
        }

        return compact('select', 'query');
    }
}