<?php 

namespace core\database\Model;

use core\App;

trait InsertArr
{
    static public function insertArr ($inputs) {
        if (is_string($inputs)) {
            return $inputs;
        }

        $className = get_called_class();
        $columns = $className::$fillable;
        $tableName = $className::getTableName($className);
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

            if(empty($value)){continue;}

            $values = implode(',', $values);
            $values = "($values)";
            $result[] = $values;
            $values = []; 
        }

        $columns = implode(', ',$columns);
        $result = implode(', ',$result);

        $sql = "INSERT INTO $tableName ( $columns ) VALUES  $result";
        //echo $sql;
        self::exec($sql);
        return $inputs;
    }
}
