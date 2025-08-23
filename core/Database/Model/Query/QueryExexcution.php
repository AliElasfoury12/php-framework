<?php 

namespace core\Database\Model\Query;

use core\App;
use core\base\_Array;
use core\Database\Model\MainModel;
use PDO;

class QueryExexcution {

    public function get (): _Array  
    {
        if(!$this instanceof MainModel) return new _Array;        
        $this->data = $this->getMainModelData();
        if(!$this->relations->empty()) $this->handleRelations();
        return $this->data;
    }

    private function getMainModelData (): _Array|null 
    {
        if(!$this instanceof MainModel) return null;
        $db = App::$app->db;

        $query = $this->query->getQuery();
        $select = $this->query->getSelect();

        $sql = "SELECT $select FROM {$this->table} $query ";
        return $db->fetch($sql);
    }

    public static function create ($inputs) 
    {
        if (is_string($inputs)) {
            return $inputs;
        }

        $className = get_called_class();
        $columns = $className::$fillable;
        $tableName = $className::getTableName();
        $values = [];

        foreach ($inputs as $column => $value) {
            if(in_array($column, $columns)) {
                if(str_contains(strtolower($column), 'password')){
                   $value = password_hash($value, PASSWORD_DEFAULT);
                }
                $values[]  =  "'$value'";
            }
        }

        $columns = implode(', ',$columns);
        $values = implode(', ',$values);

        $sql = "INSERT INTO $tableName ( $columns ) VALUES ( $values )";
        //echo $sql;
        App::$app->db->query($sql);

        return $inputs;
    }

    public static function insertArr ($inputs) 
    {
        if (is_string($inputs)) return $inputs;

        $class = get_called_class();
        $columns = $class::$fillable;
        $tableName = App::$app->model->getClassTable(static::class);
        $result = [];

        foreach ($inputs as $input) {
            foreach ($input as $column => $value) {
                if(in_array($column, $columns)) {
                    if( str_contains(strtolower($column), 'password')){
                       $value = password_hash($value, PASSWORD_DEFAULT);
                    }
                    $values[]  =  "'$value'";
                }
            }

            if(empty($value)) continue;

            $values = implode(',', $values);
            $values = "($values)";
            $result[] = $values;
            $values = []; 
        }

        $columns = implode(', ',$columns);
        $result = implode(', ',$result);

        $sql = "INSERT INTO $tableName ( $columns ) VALUES  $result";
        //echo $sql;
        App::$app->db->query($sql);
        return $inputs;
    }
}