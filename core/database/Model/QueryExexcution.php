<?php 

namespace core\database\Model;

use core\App;
use PDO;

class QueryExexcution {

    public static function get (): mixed  
    {
        $model = App::$app->model;
        $db = App::$app->db;

        $tableName = $model->getClassTable(static::class);
        $model->table = $tableName;
        $primaryKey = $db->getPK($tableName);

        $query = $model->query->getQuery();
        $select = $model->query->getSelect();

        if($model->orderBy) $orderBy = "ORDER BY $tableName".$model->orderBy;
        else $orderBy = "ORDER BY $tableName.$primaryKey ASC";
  
        $sql = "SELECT $select FROM $tableName $query $orderBy";
        //echo $sql;
        $model->query->reset();
        $model->relations->RelationsData = $db->fetch($sql);
        
        if($model->relations) {
            $model->table = $tableName;
            $model->primaryKey = $primaryKey;
            $model->orderBy = $orderBy;

            $sql = "SELECT $primaryKey FROM $tableName $query $orderBy";
            //echo $sql;
            $model->dataIds = $db->fetch($sql, PDO::FETCH_COLUMN)->implode(',');

            $model->relations->eagerLoading->handleWith( static::class);
            $model->relations->eagerLoading->handleWithCount();
        }

        return $model->relations->RelationsData;
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