<?php 

namespace core\database\Model\relations;

use core\App;

class Relations {

    use WithTrait, WithCountTrait;

    public string $relationName;
    public array $relationData;
    public string $requestedCoulmns = '*';
    public array $currentRelation;

    public function implodeColumns (string $table, array $coulmns): string 
    {
        $coulmns = array_map(fn($column) => "$table.$column" ,$coulmns);
        return implode(',', $coulmns);
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

    protected function hasOne ($class2, $foreignKey = '', $primaryKey = '') 
    {
       return $this->tablesAndKeys($class2, $foreignKey, $primaryKey,'HASONE');
    }

    protected function belongsTo ($class2, string $foreignKey = '', string $primaryKey = ''): static 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;
        $table2 = $model->getClassTable($class2);//users

        $primaryKey = $primaryKey ?: $this->getPK($table2) ;
        $foreignKey = $foreignKey ?: $this->getFK($model->table,$table2) ;

        $model->currentRelation = [
            'type' => 'BELONGSTO',
            'table2' => $table2,
            'foreignKey' => $foreignKey,
            'primaryKey' => $primaryKey
        ];

        return new static;
    }

    protected function hasMany ($class2, $foreignKey = '', $primaryKey = '') 
    {
        $this->tablesAndKeys($class2, $foreignKey, $primaryKey,'HASMANY');
        return new static;
    }

    protected function manyToMany ($relatedClass, $table2, $pivotKey, $relatedKey) 
    {
        App::$app->model->currentRelation = [
            'type' => 'MANYTOMANY',
            'table2' => $table2,
            'pivotKey' =>  $pivotKey,
            'relatedKey' => $relatedKey
        ];
        return new static;
    }

    private function tablesAndKeys ($class2, string $primaryKey, string $foreignKey, string $relation): void
    {
        $model = App::$app->model;
        $table2 = $model->getClassTable($class2);//users

        $primaryKey = $primaryKey?: $model->primaryKey;
        $foreignKey = $foreignKey ?: $this->getFK($table2, $model->table) ;

        $model->currentRelation = [
            'type' => $relation,
            'table2' => $table2,
            'foreignKey' => $foreignKey,
            'primaryKey' => $primaryKey
        ];
    }

    protected static function extraQuery (string $table = ''): array 
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