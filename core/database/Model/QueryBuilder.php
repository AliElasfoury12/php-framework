<?php 

namespace core\database\Model;

use core\App;

class QueryBuilder {
    public Query $query;
    public string $orderBy = '';
    public string $nestedSelect = '*';
    public string $table = '';
    public string $primaryKey;
    public int $pageNum = 1;
    public string $dataIds;

    public function __construct() {
        $this->query = new Query;
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
        $model = App::$app->model;
        $tableName = $model->getClassTable(static::class);
        $model->table = $tableName;
        $primaryKey = $model->relations->getPK($tableName);

        $query = $model->getQuery();
        $select = $model->handleSelect();

        if($model->orderBy){
            $orderBy = "ORDER BY $tableName".$model->orderBy;
        }else $orderBy = "ORDER BY $tableName.$primaryKey ASC";
  
        $sql = "SELECT $select FROM $tableName $query $orderBy";
        //echo $sql;
        $model->query->reset();
        $model->relations->relationData = $model->fetch($sql);
        
        if($model->relations) {
            $model->table = $tableName;
            $model->primaryKey = $primaryKey;
            $model->orderBy = $orderBy;

            $sql = "SELECT $primaryKey FROM $tableName $query $orderBy";
            //echo $sql;
            $ids = $model->fetch($sql, 'col');
            $model->dataIds = implode(',',  $ids);

            $model->relations->handleWith($model->relations->relations, static::class);
            $model->relations->handleWithCount();
        }

        if(!array_key_exists(1, $model->relations->relationData)) return (object) $model->relations->relationData[0];
        return $model->relations->relationData;
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
        $model = App::$app->model;
        $model->orderBy = '.created_at DESC';
        return new static;
    }

    public static function limit ($limit) //app\models\User
    {
        App::$app->model->query->extraQuery[] = "LIMIT $limit";
        return  new static;
    }

    public static function paginate (int $perPage) 
    {
        $offset = (App::$app->model->pageNum - 1 ) * $perPage;
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
        App::$app->model->query->select[] = $columns;
        return new static ;
    }

    public static function table (string $table): static 
    {
        App::$app->model->$table = $table;
        return new static;
    }

    public static function where (string $column ,string  $state, string $value = ''): static 
    {
        if(!$value) {
            $value = $state;
            $state = '=';
        }
        App::$app->model->query->where[] = "$column $state '$value'";
        return  new static;
    }

    public static function minRepeat (string $column) 
    {
        $tableName = App::$app->model->getClassTable(static::class);

        $sql = "SELECT $column FROM $tableName
        GROUP BY $column HAVING COUNT(*) > 1
        ORDER BY COUNT(*) ASC LIMIT 1";

        $result = App::$app->model->fetch($sql);
        return $result[0][$column];
    }

    public static function offset(string $offset): static 
    {
        App::$app->model->query->extraQuery[] = "OFFSET $offset";
        return new static;
    }

    public static function orderBy(string $column, string $type): static 
    {
        App::$app->model->orderBy = ".$column $type";
        return new static;
    }

    public static function withCount (string $columns): static 
    {
        App::$app->model->relations->withCount_relations = explode(',',$columns);
        return new static;
    }

    public static function with (array $relations)
    {
        App::$app->model->relations->relations = $relations;
        return new static;
    }
}