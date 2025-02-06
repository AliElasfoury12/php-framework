<?php 

namespace core\database\Model;

use core\App;
trait ModelMethodsTrait
{
    public array $query = ['where' => [], 'query' => [], 'select' => []];
    private static string $table = '';
    public int $pageNum = 1;
    public array $relations;

    private static function addQuery ($value, $section = 'query'): void 
    {
        App::$app->model->query[$section][] = $value;
    }

    private static function model(): MainModel 
    {
        return App::$app->model;
    }
    
    public static function all (string $columns = '*')
    {
        return static::select($columns)->get();
    }

   
    public static function find ($value, $column = 'id') 
    {
       return static::where($column, $value)->get();
    }

    public static function first () 
    {
        self::limit(1);
        return self::get();
    }

    public static function get ()  
    {
        $tableName = static::getClassTable();
        $sql = self::model()->getQuery();
        $select = self::model()->handleSelect();
  
        $sql = "SELECT $select FROM $tableName $sql";
        //echo $sql;
        self::model()->query = [];
        self::model()->relationData = self::model()->fetch($sql);
     
        if(self::model()->relations) {
            static::handleWith(self::model()->relations);
            static::handleWithCount();
        }

        if(!array_key_exists(1, self::model()->relationData)) return (object) self::model()->relationData[0];
        return self::model()->relationData;
    }

    public function getClassName (string $relation): string 
    {
        $class = ucfirst($relation);//Posts
        $class = trim($class, 's');//Post
        return str_replace('/', '', "app\models\/$class");// app\models\Post
    }

    public static function getClassTable ($nameSpace = 'app\models') //app\models\User
    {
        $table = self::$table;
        if ($table) {
            self::$table = '';
            return $table;
        }

        $class = str_replace("$nameSpace\\","" , static::class);// User
       // $class = strtolower($class);// user 
              
        return App::$app->db->getTable($class);
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
        self::$table = $table;
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

    public static function minRepeat ($column) 
    {
        $tableName = static::getClassTable();

        $sql = "SELECT $column FROM $tableName
        GROUP BY $column HAVING COUNT(*) > 1
        ORDER BY COUNT(*) ASC LIMIT 1";

        $result = self::model()->fetch($sql);
        return $result[0][$column];
    }

    public static function offset($offset) {
        self::addQuery("OFFSET $offset");
    }
}
