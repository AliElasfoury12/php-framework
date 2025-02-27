<?php 

namespace core\database\Model;

use core\App;
trait ModelMethodsTrait
{
    public array $query = [/*'where' => [], 'query' => [], 'select' => []*/];
    public string $orderBy = '';
    public string $nestedSelect = '*';
    public string $table = '';
    public string $primaryKey;
    public int $pageNum = 1;
    public array $relations;
    public string $dataIds;

    private static function addQuery ($value, string $section = 'query'): void 
    {
        App::$app->model->query[$section][] = $value;
    }

    private static function model(): MainModel 
    {
        return App::$app->model;
    }
    
    public static function all (string $columns = '*'): mixed
    {
        return static::select($columns)->get();
    }

    public static function find ($value, $column = 'id'): mixed 
    {
       return static::where($column, $value)->get();
    }

    public static function first () 
    {
        self::limit(1);
        return self::get();
    }

    public static function get (): mixed  
    {
        $model = self::model();
        $tableName = $model->getClassTable(static::class);
        $model->table = $tableName;
        $primaryKey = $model->getPK($tableName);

        $query = $model->getQuery();
        $select = $model->handleSelect();

        if($model->orderBy){
            $orderBy = "ORDER BY $tableName".$model->orderBy;
        }else $orderBy = "ORDER BY $tableName.$primaryKey ASC";
  
        $sql = "SELECT $select FROM $tableName $query $orderBy";
        //echo $sql;
        $model->query = [];
        $model->relationData = $model->fetch($sql);
        
        if($model->relations) {
            $model->table = $tableName;
            $model->primaryKey = $primaryKey;
            $model->orderBy = $orderBy;

            $sql = "SELECT $primaryKey FROM $tableName $query $orderBy";
            //echo $sql;
            $ids = $model->fetch($sql, 'col');
            $model->dataIds = implode(',',  $ids);

            static::handleWith($model->relations);
            static::handleWithCount();
        }

        if(!array_key_exists(1, $model->relationData)) return (object) $model->relationData[0];
        return $model->relationData;
    }

    public function getClassName (string $relation): string 
    {
        $class = ucfirst($relation);//Posts
        $class = trim($class, 's');//Post
        return "app\models\\$class";// app\models\Post
    }

    public function getClassTable (string $class ,string $nameSpace = 'app\models'): string //app\models\User
    {
        $class = str_replace("$nameSpace\\","" , $class);// User
        return App::$app->db->getTable($class);
    }

    public static function latest (): static 
    {
        $model =  self::model();
        $model->orderBy = '.created_at DESC';
        return new static;
    }

    public static function limit ($limit) //app\models\User
    {
        self::addQuery("LIMIT $limit");
        return  new static;
    }

    public static function paginate (int $perPage) 
    {
        $offset = (self::model()->pageNum - 1 ) * $perPage;
        self::limit($perPage);
        self::offset($offset);
        return  new static;
    }

    public static function cursorPaginate (int $perPage) 
    {
       /* $pageNumber = 1;
        $offset = ($pageNumber - 1 ) * $perPage;
        App::$app->db->query['query'][] = "LIMIT $perPage OFFSET $offset";
        $sql = "SELECT * FROM `users` ";
        */
    }

    public static function select (string $columns): static  
    {
        self::addQuery($columns, 'select');
        return new static ;
    }

    public static function table (string $table): static 
    {
        self::model()->$table = $table;
        return new static;
    }

    public static function where (string $column ,string  $state, string $value = ''): static 
    {
        if(!$value) {
            $value = $state;
            $state = '=';
        }

        self::addQuery("$column $state '$value'",'where' );
        return  new static;
    }

    public static function minRepeat (string $column) 
    {
        $tableName = static::getClassTable();

        $sql = "SELECT $column FROM $tableName
        GROUP BY $column HAVING COUNT(*) > 1
        ORDER BY COUNT(*) ASC LIMIT 1";

        $result = self::model()->fetch($sql);
        return $result[0][$column];
    }

    public static function offset(string $offset): static 
    {
        self::addQuery("OFFSET $offset");
        return new static;
    }

    public static function orderBy(string $column, string $type): static 
    {
        $model = self::model();
        $model->orderBy = ".$column $type";
        return new static;
    }



}
