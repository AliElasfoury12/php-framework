<?php 

namespace core\Database\Model\Query;

use core\App;
use PDO;

class QueryExexcution {

    public function get (): mixed  
    {
        $model = &App::$app->model;
        $model = debug_backtrace()[0]['object'];
        $db = App::$app->db;
        $model->class = static::class;
        $model->table = $model->getClassTable($model->class);
        $model->PrimaryKey  = $db->getPK($model->table);

        $query = $model->query->getQuery();
        $select = $model->query->getSelect();
        $model->query->reset();

        if($model->orderBy) $model->orderBy = "ORDER BY {$model->table} {$model->orderBy}";
        else $model->orderBy = "ORDER BY {$model->table}.{$model->PrimaryKey} ASC";
  
        $sql = "SELECT $select FROM {$model->table} $query {$model->orderBy}";
        //echo $sql;
        $model->data = $db->fetch($sql);
        
        if($model->relations) {
            $sql = "SELECT {$model->PrimaryKey} FROM {$model->table} $query {$model->orderBy}";
            //echo $sql;
            $model->ids = $db->fetch($sql, PDO::FETCH_COLUMN)->implode(',');

            $model->relations->eagerLoading->handleWith($model->class);
            $model->relations->eagerLoading->handleWithCount();
        }

        return $model->data;
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