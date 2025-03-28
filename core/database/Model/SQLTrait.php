<?php 

namespace core\database\Model;

use core\App;

trait SQLTrait
{
    public function getQuery (string $table = ''): string 
    {
        $query = App::$app->model->query;
        $wheres = '';
        $extraQuery = '';

        if(isset($query->where)) {
            $wheres = $query->where;
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
       
        if(isset($query->extraQuery)) {
            $extraQuery = $query->extraQuery;
            if(!empty($extraQuery)) {
                $extraQuery = implode(' ', $extraQuery);
            }else {
                $extraQuery = '';
            }
        }

       return  "$wheres $extraQuery";
    }

    public function handleSelect (string $table = '') 
    {
        $select = App::$app->model->query->select;
        if(isset($select)) {
            if(array_key_exists(0 , $select)) $select = $select[0];
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
}