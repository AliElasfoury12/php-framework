<?php 

namespace core\database\Model;

use core\App;

class QueryExexcution {
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
        App::$app->db->exec($sql);

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
        App::$app->db->exec($sql);
        return $inputs;
    }
}