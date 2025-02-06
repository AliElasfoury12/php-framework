<?php 

namespace core\database\Model;

use core\App;

trait Create
{
    public static function create ($inputs) {
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
}
