<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\base\_Array;
use core\base\_String;
use core\Database\Model\Query\Query;

class RelationQueryBuilder
{
    public Query $query;
    public _Array $with;

    public function __construct()
    {
        $this->query = new Query;
        $this->with = new _Array;
    }

    public function select (string $columns): static
    {
        $this->query->select->set($columns);
        return $this;
    }

    public function where (string $column ,string $opretor, string $value = ''): static 
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
        $model = App::$app->model;
        
        $method = debug_backtrace()[1]['function'];
        $relations = array_map(fn($r) => new _String("$method.$r"), $relations);

        $exsist = false;
        foreach ($model->relations->with as $value) {
            if($value == $relations[0] ){
                $exsist = true;
                break;
            }
        }

        if(!$exsist){
            $model->relations->with = $model->relations->with->merge($relations);
        }
        return  $this;
    }

    public function withCount (array $relations):static
    {
        $model = App::$app->model;
        $model->relations->currentRelation->withCount->set($relations);
        return $this;
    }

    public function groupBy (string $groupBy): static
    {
        $this->query->extraQuery[] = "GROUP BY $groupBy";
        return $this;
    }
}