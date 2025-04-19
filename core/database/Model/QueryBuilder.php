<?php 

namespace core\database\Model;

use core\App;

class QueryBuilder extends QueryExexcution 
{
    public Query $query;
    public string $orderBy = '';
    public int $pageNum = 1;
   
    public function __construct() {
        $this->query = new Query;
    }

    public static function all (string $columns = '*'): mixed
    {
        return static::select($columns)->get();
    }

    public static function cursorPaginate (int $perPage) 
    {
      
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
        App::$app->model->orderBy = '.created_at DESC';
        return new static;
    }

    public static function limit (int $limit): static //app\models\User
    {
        App::$app->model->query->extraQuery[] = "LIMIT $limit";
        return  new static;
    }

    public static function minRepeat (string $column) 
    {
        $tableName = App::$app->model->getClassTable(static::class);

        $sql = "SELECT $column FROM $tableName
        GROUP BY $column HAVING COUNT(*) > 1
        ORDER BY COUNT(*) ASC LIMIT 1";

        $result = App::$app->db->fetch($sql);
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

    public static function paginate (int $perPage): static 
    {
        $offset = (App::$app->model->pageNum - 1 ) * $perPage;
        self::limit($perPage);
        self::offset($offset);
        return  new static;
    }

    public static function select (string $columns): static
    {
        App::$app->model->query->select = $columns;
        return new static ;
    }

    public static function where (string $column ,string $opretor, string $value = ''): static 
    {
        if(!$value) {
            $value = $opretor;
            $opretor = '=';
        }
        App::$app->model->query->where[] = "$column $opretor '$value'";
        return  new static;
    }

    public static function with (array $relations): static
    {
        App::$app->model->relations->relations->set($relations);
        return new static;
    }

    public static function withCount (string $columns): static 
    {
        App::$app->model->relations->withCount_relations->set(explode(',', $columns));
        return new static;
    }

}