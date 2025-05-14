<?php 

namespace core\Database\Model\Query;

use core\App;
use HRTime\StopWatch;

class QueryBuilder extends QueryExexcution 
{
    public Query $query;
    public string $orderBy = '';
    public int $pageNum = 1;
   
    public function __construct() {
        $this->query = new Query;
    }

    public function all (string $columns = '*')
    {
        return $this->select($columns)->get();
    }

    public function cursorPaginate (int $perPage) 
    {
      
    }

    public function find ($value, $column = 'id') 
    {
       return $this->where($column, $value)->get();
    }

    public function first () 
    {
        $this->limit(1);
        return $this->get();
    }

    public function getClassName (string $relation): string 
    {
        $class = ucfirst($relation);//Posts
        $class = trim($class, 's');//Post
        return "app\models\\$class";// app\models\Post
    }

    public function getClassTable (string $class ,string $nameSpace = 'app\models'): string 
    {
        $class = str_replace("$nameSpace\\","" , $class);
        return App::$app->db->getTable($class);
    }

    public function latest ()  
    {
        App::$app->model->orderBy = '.created_at DESC';
        return $this;
    }

    public function limit (int $limit)  
    {
        App::$app->model->query->extraQuery[] = "LIMIT $limit";
        return  $this;
    }

    public function minRepeat (string $column) 
    {
        $tableName = App::$app->model->getClassTable(static::class);

        $sql = "SELECT $column FROM $tableName
        GROUP BY $column HAVING COUNT(*) > 1
        ORDER BY COUNT(*) ASC LIMIT 1";

        $result = App::$app->db->fetch($sql);
        return $result[0][$column];
    }

    public function offset(string $offset)  
    {
        App::$app->model->query->extraQuery[] = "OFFSET $offset";
        return $this;
    }

    public function orderBy(string $column, string $type)  
    {
        App::$app->model->orderBy = ".$column $type";
        return $this;
    }

    public function paginate (int $perPage)  
    {
        $offset = (App::$app->model->pageNum - 1 ) * $perPage;
        $this->limit($perPage);
        $this->offset($offset);
        return  $this;
    }

    public function select (string $columns)  
    {
        App::$app->model->query->select = $columns;
        return $this;
    }

    public function where (string $column ,string $opretor, string $value = '')  
    {
        if(!$value) {
            $value = $opretor;
            $opretor = '=';
        }
        App::$app->model->query->where[] = "$column $opretor '$value'";
        return  $this;
    }

    public function with (array $relations) 
    {
        App::$app->model->relations->with->set($relations);
        return $this;
    }

    public function withCount (string $columns)  
    {
        App::$app->model->relations->withCount->set(explode(',', $columns));
        return $this;
    }

}