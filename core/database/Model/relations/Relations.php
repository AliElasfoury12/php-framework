<?php 

namespace core\database\Model\relations;

use core\App;

class Relations {

    use WithTrait, WithCountTrait;

    public string $relationName;
    public array $relationData;
    public string $requestedCoulmns = '*';
    public array $currentRelation;
    public string $mainTable;

    public function implodeColumns (string $table, array $coulmns): string 
    {
        $coulmns = array_map(fn($column) => "$table.$column" ,$coulmns);
        return implode(',', $coulmns);
    }

    public function getPK ($table) 
    {
        $sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        //echo "$sql <br>";
        $result = App::$app->db->fetch($sql);
        return $result[0]["Column_name"];
    }

    public function getFK ($table, $keyPart) 
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

    protected function belongsTo ($class2, $foreignKey = '', $primaryKey = '') 
    {
        //table1 posts belongsTO table2 users
        $model = App::$app->model;
        $table2 = $class2::getClassTable();//users

        !$primaryKey ? $primaryKey = $this->getPK($table2) : '';
        !$foreignKey ? 
        $foreignKey = $this->getFK($model->mainTable, substr($table2, 0, -1)) 
        : '';

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
        $table1 = $relatedClass::getClassTable();
        App::$app->model->currentRelation = [
            'type' => 'MANYTOMANY',
            'table2' => $table2,
            'pivotKey' =>  $pivotKey,
            'relatedKey' => $relatedKey
        ];
        return new static;
    }

    private function tablesAndKeys ($class2, $primaryKey, $foreignKey, $relation): void
    {
        $model = App::$app->model;
        $table1 = $model->mainTable;
        $table2 = $class2::getClassTable();//users

        $primaryKey = $primaryKey?: $this->getPK($table1);
        $foreignKey = $foreignKey ?: $this->getFK($table2, $table1) ;

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