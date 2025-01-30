<?php 

namespace core\database\Model\relations;

use core\App;

class Relations {

    use WithTrait, WithCountTrait;

    public $relationName;
    public $relationData = [];
    public $requestedCoulmns = [];

    public function implodeColumns ($table, $coulmns) 
    {
        $coulmns = array_map(fn($column) => "$table.$column" ,$coulmns);
        return implode(' , ', $coulmns);
    }

    public function getPK ($table) 
    {
        $sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        $result = App::$app->db->fetch($sql);
        return $result[0]["Column_name"];
    }

    public function getFK ($table, $keyPart) 
    {
        $sql = "SHOW KEYS FROM $table WHERE Key_name Like '%$keyPart%'";
        echo "$sql <br>";
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

        $class = get_called_class();

        $table1 = $class::getTableName();//posts
        $table2 = $class2::getTableName();//users

        !$primaryKey ? $primaryKey = $this->getPK($table2) : '';
        !$foreignKey ? 
        $foreignKey = $this->getFK($table1, substr($table2, 0, -1)) 
        : '';

        return ['BELONGSTO', $table1, $table2, $foreignKey, $primaryKey];
    }

    protected function hasMany ($class2, $foreignKey = '', $primaryKey = ''): array 
    {
        return $this->tablesAndKeys($class2, $foreignKey, $primaryKey,'HASMANY');
    }

    protected function manyToMany ($relatedClass, $table2, $pivotKey, $relatedKey): array 
    {
        $table1 = $relatedClass::getTableName();
        return ['MANYTOMANY', $table1, $table2, $pivotKey, $relatedKey];
    }

    public function tablesAndKeys ($class2, $primaryKey, $foreignKey, $relation): array 
    {
        $class = get_called_class();

        $table1 = $class::getTableName();//posts
        $table2 = $class2::getTableName();//users

        !$primaryKey ? $primaryKey = $this->getPK($table1) : '';
        !$foreignKey ? 
        $foreignKey = $this->getFK($table2, substr($table2, 0, -1)) 
        : '';

        return [$relation, $table1, $table2, $foreignKey, $primaryKey];
    }
}