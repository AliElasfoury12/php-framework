<?php 

namespace core\database\Model\relations;

use core\App;

class Relations {

    use WithTrait, WithCountTrait;

    public string $relationName;
    public array $relationData;
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

    public function getPK (string $table): mixed 
    {
        $sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        //echo "$sql <br>";
        $result = App::$app->db->fetch($sql);
        return $result[0]["Column_name"];
    }

    public function getFK (string $table, string $keyPart): mixed 
    {
        $keyPart = rtrim($keyPart, 's');
        $sql = "SHOW KEYS FROM $table WHERE Key_name Like '%$keyPart%'";
        //echo "$sql <br>";
        $result = App::$app->db->fetch($sql);
        return $result[0]["Column_name"];
    }

    protected function hasOne (string $class2, string $foreignKey = '', string $primaryKey = ''): static
    {
        $model = App::$app->model;
        $this->tablesAndKeys($class2, $foreignKey, $primaryKey,$model->relationTypes::HASONE);
        return new static;
    }

    protected function belongsTo (string $class2, string $foreignKey = '', string $primaryKey = ''): static 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;
        $table2 = $model->getClassTable($class2);//users

        $primaryKey = $primaryKey ?: $this->getPK($table2) ;
        $foreignKey = $foreignKey ?: $this->getFK($model->table,$table2) ;

        $currentRelation = $model->currentRelation;
        $currentRelation->type = $model->relationTypes::BELONGSTO;
        $currentRelation->table2 = $table2;
        $currentRelation->foreignKey = $foreignKey;
        $currentRelation->primaryKey = $primaryKey;

        return new static;
    }

    protected function hasMany (string $class2, string $foreignKey = '', string $primaryKey = ''): static 
    {
        $model = App::$app->model;
        $this->tablesAndKeys($class2, $foreignKey, $primaryKey,$model->relationTypes::HASMANY);
        return new static;
    }

    protected function manyToMany (string $relatedClass, string $pivotTable, string $pivotKey, string $relatedKey): static 
    {
        $model = App::$app->model;

        $currentRelation = $model->currentRelation;
        $currentRelation->type = $model->relationTypes::MANYTOMANY;
        $currentRelation->table2 = $model->getClassTable($relatedClass);
        $currentRelation->primaryKey = $model->getPK($currentRelation->table2);
        $currentRelation->pivotTable = $pivotTable;
        $currentRelation->pivotKey = $pivotKey;
        $currentRelation->relatedKey = $relatedKey;

        return new static;
    }

    private function tablesAndKeys (string $class2, string $primaryKey, string $foreignKey, string $relation): void
    {
        $model = App::$app->model;
        $table2 = $model->getClassTable($class2);//users

        $primaryKey = $primaryKey?: $model->primaryKey;
        $foreignKey = $foreignKey ?: $this->getFK($table2, $model->table) ;

        $currentRelation = $model->currentRelation;
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
            $query = $model->getQuery();
            if(isset($model->query['where'])){
                $query = str_replace('WHERE', 'AND', $query);
            }

            if(isset($model->query['select'])){
                $select = $model->handleSelect($table);
            }
        }

        return compact('select', 'query');
    }
}