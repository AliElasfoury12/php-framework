<?php 

namespace core\database\Model;

use core\App;

trait InsertArr
{
    public static function insertArr ($inputs) {
        if (is_string($inputs)) return $inputs;

        $columns = static::$fillable;
        $tableName = static::getTableName();
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
