<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\base\_String;
use core\Database\Model\Relations\CurrentRelation;

class EagerLoadingSQLBuilder {
    public function buildSQL (_Array $relations, string $class): void
    {
        $model = App::$app->model;
        $currentRelation = &$model->relations->currentRelation;
        $class2 = $class;
        $sql = new _String;
        $relation = new _String();

        for ($i=0; $i < $relations->size; $i++) { 
            $relation->set($relations[$i]);
            $columns = $this->getColumns($relation, $currentRelation);

            call_user_func([new $class2, $currentRelation->name]);
            $class2 = $currentRelation->model2;
            $table1 = $currentRelation->table1;

            $currentRelation->sql = $this->assembleSQL($table1, $relations, $i, $columns,$sql);

            if(!$currentRelation->withCount->empty()) {
               $currentRelation = $this->handleWithCount($class2,$sql);
            }
            $relations[$i] = clone $currentRelation;
            $currentRelation->reset();
        }
       // App::dump($relations);
    }

    private function getColumns (_String $relation, CurrentRelation $currentRelation): string|_String 
    {
        if($relation->contains(':')){
            $colonPostion = $relation->position(':');
            $currentRelation->name = $relation->subString(0, $colonPostion);
            $columns = $relation->subString($colonPostion+1);
        }else{
            $currentRelation->name = $relation;
            $columns = '';
        }
        return $columns;
    }

    public function assembleSQL (string $table1, _Array $relations, int $i, string $columns, string &$sql, bool $iswithCount = false): string
    {
        $model = App::$app->model;
        $PK = $model->PrimaryKey;
        $ids = $model->ids;
        $orderBy = $model->orderBy;
        $currentRelation = $model->relations->currentRelation;
        $relationsTypes = $model->relations->Types;
        $table = $model->table;
        $type = $currentRelation->type;
        $select = '';
        $extraQuery = '';

        if( $i > 0 && 
           // !$iswithCount &&
            $relations[$i-1]->type == $relationsTypes::MANYTOMANY &&
            str_contains($sql,"FROM $table1")
            ) {$table1 = "alias$i";}

        switch ($type) {
            case $relationsTypes::BELONGSTO:
                if($i == $relations->size - 1 && $columns) $model->relations->BelongsTo->select($columns);
                $sql .= $this->buildBelongsToSQL($table1, $select, $extraQuery);
            break;

            case  $relationsTypes::HASMANY:
                if(($i == $relations->size - 1 && $columns) || $iswithCount) {
                    $model->relations->HasMany->select($columns);
                }
                $sql .= $this->buildHasManySQL($table1, $select, $extraQuery);
            break;

            default:
                if(($i == $relations->size - 1 && $columns) || $iswithCount) {
                    $model->relations->ManyToMany->select($columns);
                }
                $sql .= $this->buildManyToManySQL($table1, $i, $select, $extraQuery, $iswithCount, $relations);
            break;
        }

        return "SELECT $select , $table.$PK AS mainKey FROM $table $sql WHERE $table.$PK IN ($ids) $extraQuery $orderBy";
    }
   
    private function buildBelongsToSQL (string $table1, string &$select, &$extraQuery): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK2 = $currentRelation->PK2;
        $FK1 = $currentRelation->FK1;
        $table2 = $currentRelation->table2;

        $select = $model->relations->BelongsTo->query->getSelect($table2);
        $extraQuery = $model->relations->BelongsTo->query->getQuery($table2);
        $model->relations->BelongsTo->query->reset();

        return "INNER JOIN $table2 ON $table1.$FK1 = $table2.$PK2 ";
    }

    private function buildHasManySQL (string $table1, string &$select, &$extraQuery): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK1 = $currentRelation->PK1;
        $FK2 = $currentRelation->FK2;
        $table2 = $currentRelation->table2;

        $select = $model->relations->HasMany->query->getSelect($table2);
        $extraQuery = $model->relations->HasMany->query->getQuery($table2);
        $model->relations->HasMany->query->reset();

        return "INNER JOIN $table2 ON $table1.$PK1 = $table2.$FK2 ";
    }

    private function buildManyToManySQL (string $table1, int $i, string &$select, &$extraQuery, bool $iswithCount, $relations): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK1 = $currentRelation->PK1;
        $pivotTable = $currentRelation->pivotTable;
        $pivotKey = $currentRelation->pivotKey;
        $relatedKey = $currentRelation->relatedKey;
        $table2 = $currentRelation->table2;
        $PK2 = $currentRelation->PK2;

        $j = $i + 1;

        $select = $model->relations->ManyToMany->query->getSelect("alias$j");
        if($select != 'COUNT(*) AS count'){
            $select .= ", $pivotTable.$pivotKey AS pivot, $pivotTable.$relatedKey AS related";
        }
        $extraQuery = $model->relations->ManyToMany->query->getQuery();
        $model->relations->ManyToMany->query->reset();

        $withCountSQL = "INNER JOIN $pivotTable ON $table1.$PK1 = $pivotTable.$pivotKey"; 
        if($iswithCount) return $withCountSQL;
        
        return "$withCountSQL INNER JOIN $table2 AS alias$j ON alias$j.$PK2 = $pivotTable.$relatedKey ";
    }

    private function handleWithCount (string $class2, string $sql): CurrentRelation 
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $lastRelation = clone $currentRelation;
        $withCountRelations = clone $lastRelation->withCount;
        $relationsTypes = $model->relations->Types;


        foreach ($withCountRelations as $key => &$withCountRelation) {
           call_user_func([new $class2, $withCountRelation]);

           $table1 = $currentRelation->table1;
           $PK1 = $currentRelation->PK1;

            $i = $key+1;
           if($lastRelation->type == $relationsTypes::MANYTOMANY ) $table1 = "alias$i";

            if($currentRelation->type  == $relationsTypes::HASMANY){
                $model->relations->HasMany->groupBy("$table1.$PK1") ;
            }else $model->relations->ManyToMany->groupBy("$table1.$PK1");
        
            $sql = $this->assembleSQL(
                $table1,
                $withCountRelations,
                $key,
                "COUNT(*) AS count",
                $sql,
                true);
                
            $currentRelation->sql = $sql;
            $currentRelation->name = $withCountRelation;
            $withCountRelation = clone $currentRelation;
            $sql = '';
        }

        $currentRelation = $lastRelation;
        $currentRelation->withCount = $withCountRelations;
        return $currentRelation;
    }
}