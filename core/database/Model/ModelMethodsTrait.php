<?php 

namespace core\database\Model;

use core\App;
trait ModelMethodsTrait
{
    public array $query = ['where' => [], 'query' => [], 'select' => []];
    private static string $table = '';
    public int $pageNum = 1;

    private static function addQuery ($value, $section = 'query'): void 
    {
        App::$app->model->query[$section][] = $value;
    }

    private static function model() 
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
        //$tableName =  static::getTableName();
       // $sql = "SELECT * FROM $tableName WHERE $column = '$value'";
       // return  self::model()->fetch($sql, 'obj')[0];
    }

    public static function first () 
    {
        self::limit(1);
        return self::get();
    }

    public static function get ()  
    {
        $tableName = static::getTableName();
        $sql = self::model()->getQuery();
        $select = self::model()->handleSelect();
  
        $sql = "SELECT $select FROM $tableName $sql";
        //echo $sql;
        self::model()->query = [];
        $data = self::model()->fetch($sql);
        if(count($data) == 1) return (object) $data[0];
        return $data ;
    }

    public static function getTableName ($nameSpace = 'app\models') //app\models\User
    {
        $table = self::$table;
        if ($table) {
            self::$table = '';
            return $table;
        }

        $class = get_called_class();
        $class = str_replace($nameSpace,"" , $class);// \User
        $class = stripslashes($class);// User
        $class = strtolower($class);// user 
        
        $sql = "show TABLES LIKE '$class'";
        $exsists = self::model()->fetch($sql);
        !$exsists ?  $table = $class.'s':  $table = $class;
    
        return  $table;
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
        $tableName = static::getTableName();

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
