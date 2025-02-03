<?php 

namespace core\database\Model;

use core\App;

trait SQLTrait
{
    public function getQuery ($table = '') 
    {
        $query = App::$app->model->query;
        $wheres = '';
        $extraQuery = '';

        if($query['where']) {
            $wheres = $query['where'];
            if(!empty($wheres)) {
                if(count($wheres) > 1) {
                   if($table) $wheres = array_map(fn($w) => "$table.$w", $wheres);
                   
                    $wheres = implode(' AND ', $wheres);
                }
                $wheres = $wheres[0];
                $wheres = "WHERE $wheres";
            }else {
                $wheres = '';
            }
        }
       
        if($query['query']) {
            $extraQuery = $query['query'];
            if(!empty($extraQuery)) {
                $extraQuery = implode(' ', $extraQuery);
            }else {
                $extraQuery = '';
            }
        }

       return  "$wheres $extraQuery";
    }

    public function handleSelect ($table = '') 
    {
        $select = null;
        if(App::$app->model->query['select']) {
            if(array_key_exists(0 ,App::$app->model->query['select']))
            $select = App::$app->model->query['select'][0];
        }

        if($select){
            if($table && $select) {
                $select = explode(',', $select);
                $select = array_map(fn($field) => "$table.$field", $select);
                $select = implode(',', $select);
            }
            return $select;
        }

        if($table) return "$table.*";

        return '*';
    }

    public function getRequestedColumns ($table = ''): mixed 
    {
        $model = &App::$app->model;
        if (array_key_exists($model->relationName,  $model->requestedCoulmns)) 
        {
            $requestedCoulmns = $model->requestedCoulmns[$model->relationName];
            if($table) {
                $requestedCoulmns = explode(',', $requestedCoulmns);
                return $model->implodeColumns($table, $requestedCoulmns);
            }
            return $requestedCoulmns;
        }

       if($table && $model->requestedCoulmns) return "$table.*";

       return '*';
    }
}