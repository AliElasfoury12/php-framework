<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\base\_String;
use core\Database\Model\Relations\CurrentRelation;

class EagerLoadingSQLBuilder {
    public function buildSQL (_Array $relations, string $class, bool $with = false, CurrentRelation $currentRelation = null, string $sql = ''): void
    {
        $model = App::$app->model;
        $currentRelation = &$model->relations->currentRelation;
        $class2 = $class;
        $relation = new _String();
        $mainSql = $sql;

        for ($i=0; $i < $relations->size; $i++) { 
            if($with) {
                $sql = $mainSql;
                $class2 = $class;
            }

            $relation->set($relations[$i]);
            $columns = $this->getColumns($relation, $currentRelation);

            call_user_func([new $class2, $currentRelation->name]);
            $class2 = $currentRelation->model2;

            $currentRelation->sql = $this->assembleSQL($relations, $i, $columns,$sql, false, $with);
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

    public function assembleSQL (_Array $relations, int $i, string $columns, string &$sql, bool $isWithCount = false, bool $isWith = false): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $table1 = $currentRelation->table1;
        $aliasTable2 = '';

        $lastRelation = $i > 0 ? $relations[$i - 1] : null;
        if($isWith || $isWithCount) $lastRelation = $i > 0 ? $relations[0] : $currentRelation;

        $this->handleAlias($lastRelation,$i,$currentRelation,$table1,$aliasTable2);

        $isColumnsAndNotWithCount = ($i == $relations->size - 1) && $columns && !$isWithCount;
        return $this->buildRelationSQL($isColumnsAndNotWithCount,$columns,$table1,$isWithCount,$aliasTable2,$sql);
    }

    private function handleAlias (?CurrentRelation $lastRelation,int $i, CurrentRelation $currentRelation, string &$table1, string &$aliasTable2): void 
    {
        $model = App::$app->model;
        $table2 = $currentRelation->table2;

        if($lastRelation){
            preg_match_all('/\s*alias\d*\s*/', $lastRelation->sql, $matches);
            if($matches[0]){
                $matches = $matches[0];
                $lastAlias = $matches[count($matches) - 1];
                $lastAlias = str_replace('alias','', $lastAlias);
                $j = $lastAlias+1;
            }else $j = 2;
        }else $j = 1;

        if($table2 == $model->table) {
            $currentRelation->alias = "alias$j";
            $aliasTable2 = "alias$j";
        }

        if(!$lastRelation) return;

        if($lastRelation->alias) $table1 = $lastRelation->alias;

        if(str_contains($lastRelation->sql,"INNER JOIN $table2")) {
            $currentRelation->alias = "alias$j";
            $aliasTable2 = "alias$j";
        }
    }

    private function buildRelationSQL (bool $isColumnsAndNotWithCount,string $columns,string $table1,bool $isWithCount,string $aliasTable2,string &$sql): string 
    {
        $model = App::$app->model;
        $relationsTypes = $model->relations->Types;
        $type = $model->relations->currentRelation->type;
        $select = '';
        $extraQuery = '';
        $ids = $model->ids;
        $orderBy = $model->orderBy;
        $PK = $model->PrimaryKey;
        $table = $model->table;
        if($isWithCount) $select = 'COUNT(*) AS count';
        
        switch ($type) {
            case $relationsTypes::BELONGSTO:
                if($isColumnsAndNotWithCount) $model->relations->BelongsTo->select($columns);
                $sql .= $this->buildBelongsToSQL($table1, $select, $extraQuery, $isWithCount, $aliasTable2);
            break;

            case  $relationsTypes::HASMANY:
                if($isColumnsAndNotWithCount) $model->relations->HasMany->select($columns);
                $sql .= $this->buildHasManySQL($table1, $select, $extraQuery, $isWithCount, $aliasTable2);
            break;

            default:
                if($isColumnsAndNotWithCount) $model->relations->ManyToMany->select($columns);
                   
                $sql .= $this->buildManyToManySQL($table1,$select,$extraQuery,$isWithCount,$aliasTable2);
            break;
        }
        return "SELECT $select , $table.$PK AS mainKey FROM $table $sql WHERE $table.$PK IN ($ids) $extraQuery $orderBy";
    }
   
    private function buildBelongsToSQL (string $table1, string &$select, string &$extraQuery, bool $iswithCount, string $aliasTable2): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK2 = $currentRelation->PK2;
        $FK1 = $currentRelation->FK1;
        $table2 = $currentRelation->table2;

        if(!$iswithCount) $select = $model->relations->BelongsTo->query->getSelect($table2);
        
        $extraQuery = $model->relations->BelongsTo->query->getQuery($aliasTable2 ?: $table2);
        $model->relations->BelongsTo->query->reset();

        if($aliasTable2){
            return "INNER JOIN $table2 AS $aliasTable2 ON $table1.$FK1 = $aliasTable2.$PK2 ";
        }

        return "INNER JOIN $table2 ON $table1.$FK1 = $table2.$PK2 ";
    }

    private function buildHasManySQL (string $table1, string &$select, string &$extraQuery,bool $iswithCount, string $aliasTable2): string
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $PK1 = $currentRelation->PK1;
        $FK2 = $currentRelation->FK2;
        $table2 = $currentRelation->table2;

        if(!$iswithCount) $select = $model->relations->HasMany->query->getSelect($aliasTable2 ?: $table2);

        $extraQuery = $model->relations->HasMany->query->getQuery($table2);
        $model->relations->HasMany->query->reset();

        if($aliasTable2){
            return "INNER JOIN $table2 AS $aliasTable2 ON $table1.$PK1 = $aliasTable2.$FK2 ";
        }

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

    public function buildWithCountSQL (CurrentRelation &$currentRelation,string $class2, string $sql): void
    {
        $model = App::$app->model;
        $lastRelation = clone $currentRelation;
        $withCountRelations = $lastRelation->withCount;
        $currentRelation->withCount->reset();
        $currentRelation->with->reset();
        $relationsTypes = $model->relations->Types;

        foreach ($withCountRelations as $key => &$withCountRelation) {
           call_user_func([new $class2, $withCountRelation]);
           $table1 = $currentRelation->table1;
           $PK1 = $currentRelation->PK1;

            if($currentRelation->type  == $relationsTypes::HASMANY){
                $model->relations->HasMany->groupBy("$table1.$PK1") ;
            }else $model->relations->ManyToMany->groupBy("$table1.$PK1");
        
            $sql = $this->assembleSQL(
                $withCountRelations,$key,'',
                $sql,true);
                
            $currentRelation->sql = $sql;
            $currentRelation->name = $withCountRelation;
            $currentRelation->withCount->reset();
            $currentRelation->with->reset();
            $withCountRelation = clone $currentRelation;
            $sql = '';
        }

        $currentRelation = $lastRelation;
        $currentRelation->withCount = $withCountRelations;
    }

    private function buildWithSQL (CurrentRelation &$currentRelation, string $class2, string $sql): void
    {
        $current_relation = clone $currentRelation;
        $withRelations = $current_relation->with;

        $currentRelation->with->reset();
        $currentRelation->withCount->reset();
        $this->buildSQL($withRelations,$class2,true,$currentRelation,$sql);
        $current_relation->with = $withRelations;
        $currentRelation = clone $current_relation;
    }
}