<?php 

namespace core\Database\Model\Query;

use core\App;
use core\base\_String;
use core\Database\Model\MainModel;

class QueryBuilder extends QueryExexcution 
{
    public Query $query;
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

    public function groupBy (string $groupBy): static
    {
        $this->query->extraQuery[] = "GROUP BY $groupBy";
        return $this;
    }

    public function latest (): static  
    {
        $this->query->orderBy->set('.created_at DESC') ;
        return $this;
    }

    public function limit (int $limit)  
    {
        $this->query->extraQuery[] = "LIMIT $limit";
        return  $this;
    }

    public function minRepeat (string $column) 
    {
        $tableName = $this->getClassTable(static::class);

        $sql = "SELECT $column FROM $tableName
        GROUP BY $column HAVING COUNT(*) > 1
        ORDER BY COUNT(*) ASC LIMIT 1";

        $result = App::$app->db->fetch($sql);
        return $result[0][$column];
    }

    public function offset(string $offset)  
    {
        $this->query->extraQuery[] = "OFFSET $offset";
        return $this;
    }

    public function orderBy(string $column, string $type): static  
    {
        $this->query->orderBy->set(".$column $type");
        return $this;
    }

    public function paginate (int $perPage)  
    {
        $offset = ($this->pageNum - 1 ) * $perPage;
        $this->limit($perPage);
        $this->offset($offset);
        return  $this;
    }

    public function select (string $columns): static  
    {
        $this->query->select->set($columns);
        return $this;
    }

    public function where (string $column ,string $opretor, string $value = '')  
    {
        if(!$value) {
            $value = $opretor;
            $opretor = '=';
        }
        $this->query->where[] = "$column $opretor '$value'";
        return  $this;
    }

    public function with (array $relations): static 
    {
        if($this instanceof MainModel){
            foreach ($relations as $relation) {
                if($this->relations[$relation]) continue;
                $relation = new _String($relation);
                if($relation->contains(':')){
                    $dotPostion = $relation->position(':');
                    $columns = $relation->subString($dotPostion+1, $relation->length());
                    $relation = $relation->replace(":$columns", '');
                    $relation = $relation->value();
                    $this->$relation();
                    $this->relations[$relation]->model->select($columns);
                }else {
                    $relation = $relation->value();
                    $this->$relation();
                }
            }
        }
        return $this;
    }

    public function withCount (array $relations)  
    {
        //if($this instanceof MainModel) $this->relations->withCount->set($relations);
        return $this;
    }

}