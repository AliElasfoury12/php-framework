<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\base\_String;
use core\Database\Model\Relations\CurrentRelation;
use stdClass;

class EagerLoadingSQLBuilder {
    public function buildSQL (_Array $relations, string $class, bool $with = false, CurrentRelation $currentRelation = null, string $sql = ''): void
    {
        $model = App::$app->model;
        $currentRelation = &$model->relations->currentRelation;
        $class2 = $class;
        $relation = new _String();
        $mainRelation = clone $currentRelation;
        $mainSql = $sql;

        for ($i=0; $i < $relations->size; $i++) { 
            $alias='';
            if($with) {
                $sql = $mainSql;
                $class2 = $class;
                if($mainRelation->alias){
                    $alias = $mainRelation->alias;
                } 
            }

            $relation->set($relations[$i]);
            $columns = $this->getColumns($relation, $currentRelation);

            call_user_func([new $class2, $currentRelation->name]);
            $class2 = $currentRelation->model2;
            $table1 = $alias ?: $currentRelation->table1;

            $currentRelation->sql = $this->assembleSQL($table1, $relations, $i, $columns,$sql);
            $this->buildSubQueries($currentRelation,$class2,$sql);
            $relations[$i] = clone $currentRelation;

            $currentRelation->reset();
        }
       //if(!$with) $relations->print();
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
        $aliasTable2 = '';

        $lastRelation = $i > 0 ? $relations[$i - 1] : '';

        $this->handleAlias(
            $lastRelation,
            $i,
            $currentRelation,
            $model,
            $table1,
            $aliasTable2);

        if($iswithCount) $select = 'COUNT(*) AS count';

        switch ($type) {
            case $relationsTypes::BELONGSTO:
                if($i == $relations->size - 1 && $columns) $model->relations->BelongsTo->select($columns);
                $sql .= $this->buildBelongsToSQL($table1, $select, $extraQuery, $iswithCount);
            break;

            case  $relationsTypes::HASMANY:
                if($i == $relations->size - 1 && $columns) $model->relations->HasMany->select($columns);
                $sql .= $this->buildHasManySQL($table1, $select, $extraQuery, $iswithCount);
            break;

            default:
                if($i == $relations->size - 1 && $columns) $model->relations->ManyToMany->select($columns);
                   
                $sql .= $this->buildManyToManySQL(
                    $table1,
                    $select,
                    $extraQuery,
                    $iswithCount,
                    $aliasTable2);
            break;
        }

        return "SELECT $select , $table.$PK AS mainKey FROM $table $sql WHERE $table.$PK IN ($ids) $extraQuery $orderBy";
    }

    private function handleAlias ($lastRelation, $i, $currentRelation, $model, &$table1, &$aliasTable2) 
    {
        $table2 = $currentRelation->table2;
        $j = $i + 1;

        if($lastRelation && $lastRelation->alias) $table1 = $lastRelation->alias;

        if($lastRelation){
            $isTableExsistsInSql = $table2 == $model->table || str_contains($lastRelation->sql,"INNER JOIN $table2");
            if($isTableExsistsInSql) {
                $currentRelation->alias = "alias$j";
                $aliasTable2 = "alias$j";
            }
        }else if($table2 == $model->table) {
            $currentRelation->alias = "alias$j";
            $aliasTable2 = "alias$j";
        }
    }
   
    private function buildBelongsToSQL (string $table1, string &$select, string &$extraQuery, bool $iswithCount): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK2 = $currentRelation->PK2;
        $FK1 = $currentRelation->FK1;
        $table2 = $currentRelation->table2;

        if(!$iswithCount) $select = $model->relations->BelongsTo->query->getSelect($table2);
        
        $extraQuery = $model->relations->BelongsTo->query->getQuery($table2);
        $model->relations->BelongsTo->query->reset();

        return "INNER JOIN $table2 ON $table1.$FK1 = $table2.$PK2 ";
    }

    private function buildHasManySQL (string $table1, string &$select, string &$extraQuery,bool $iswithCount): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK1 = $currentRelation->PK1;
        $FK2 = $currentRelation->FK2;
        $table2 = $currentRelation->table2;

        if(!$iswithCount) $select = $model->relations->HasMany->query->getSelect($table2);

        $select = $model->relations->HasMany->query->getSelect($table2);
        $extraQuery = $model->relations->HasMany->query->getQuery($table2);
        $model->relations->HasMany->query->reset();

        return "INNER JOIN $table2 ON $table1.$PK1 = $table2.$FK2 ";
    }

    private function buildManyToManySQL (string $table1, string &$select, &$extraQuery, bool $iswithCount, string $aliasTable2 = ''): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK1 = $currentRelation->PK1;
        $pivotTable = $currentRelation->pivotTable;
        $pivotKey = $currentRelation->pivotKey;
        $relatedKey = $currentRelation->relatedKey;
        $PK2 = $currentRelation->PK2;
        $table2 = $currentRelation->table2;

        $withCountSQL = "INNER JOIN $pivotTable ON $table1.$PK1 = $pivotTable.$pivotKey"; 

        if(!$iswithCount) {
            $select = $model->relations->ManyToMany->query->getSelect($aliasTable2 ?: $table2);
            $select .= ", $pivotTable.$pivotKey AS pivot, $pivotTable.$relatedKey AS related";
        }
       
        $extraQuery = $model->relations->ManyToMany->query->getQuery();
        $model->relations->ManyToMany->query->reset();

        if($iswithCount) return $withCountSQL;
        if($aliasTable2){
            return "$withCountSQL INNER JOIN $table2 AS $aliasTable2 ON $aliasTable2.$PK2 = $pivotTable.$relatedKey ";
        }
        
        return "$withCountSQL INNER JOIN $table2 ON $table2.$PK2 = $pivotTable.$relatedKey ";
    }

    private function buildSubQueries (CurrentRelation &$currentRelation,string $class2, string $sql): void 
    {
        if(!$currentRelation->with->empty()) {
            $this->buildWithSQL($currentRelation,$class2,$sql);
        }
        if(!$currentRelation->withCount->empty()) {
            $this->buildWithCountSQL($currentRelation,$class2,$sql);
        }
    }

    private function buildWithCountSQL (CurrentRelation &$currentRelation,string $class2, string $sql): void
    {
        $model = App::$app->model;
        $lastRelation = clone $currentRelation;
        $withCountRelations = clone $lastRelation->withCount;
        $lastRelation->withCount->reset();
        $relationsTypes = $model->relations->Types;

        foreach ($withCountRelations as $key => &$withCountRelation) {
           call_user_func([new $class2, $withCountRelation]);
           $table1 = $currentRelation->table1;
           $PK1 = $currentRelation->PK1;

            if($currentRelation->type  == $relationsTypes::HASMANY){
                $model->relations->HasMany->groupBy("$table1.$PK1") ;
            }else $model->relations->ManyToMany->groupBy("$table1.$PK1");
        
            $sql = $this->assembleSQL(
                $table1,
                $withCountRelations,
                $key,
                '',
                $sql,
                true);
                
            $currentRelation->sql = $sql;
            $currentRelation->name = $withCountRelation;
            $withCountRelation = clone $currentRelation;
            $sql = '';
        }

        $currentRelation = $lastRelation;
        $currentRelation->withCount = $withCountRelations;
    }

    private function buildWithSQL (CurrentRelation &$currentRelation, string $class2, string $sql): void
    {
        $withRelations = clone $currentRelation->with;
        $current_relation = clone $currentRelation;
        $currentRelation->with->reset();
        $currentRelation->withCount->reset();
        $this->buildSQL($withRelations,$class2,true,$currentRelation,$sql);
        $current_relation->with = $withRelations;
        $currentRelation = clone $current_relation;
    }
}