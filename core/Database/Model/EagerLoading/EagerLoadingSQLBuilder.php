<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\base\_Srting;
use core\Database\Model\MainModel;
use core\Database\Model\Relations\CurrentRelation;
use core\Database\Model\Relations\RELATIONSTYPE;

class EagerLoadingSQLBuilder {
    public function buildSQL (_Array $relations, string $class, RELATIONSTYPE $relationsTypes): void
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $table = $model->table;
        $class2 = $class;
        $sql = '';
        $relation = new _Srting();

        for ($i=0; $i < $relations->size; $i++) { 
            $relation->set($relations[$i]);
            $columns = $this->getColumns($relation, $currentRelation);

            call_user_func([new $class2, $currentRelation->name]);
            $class2 = $currentRelation->model2;
            $table1 = $currentRelation->table1;

            if($i > 0 && ($table1 === $table || $relations[$i - 1]->type == $relationsTypes::MANYTOMANY)) {
                $j = $i - 1;
                $table1 = "alias$j";
            }

            $this->assembleSQL($table1, $relations, $i, $columns,$sql);
            if(!$currentRelation->withCount->empty()) App::dump($currentRelation->withCount);

            $relations[$i] = clone $currentRelation;
            $currentRelation->reset();
        }
        //App::dump((array) $relations);
    }

    private function getColumns (_Srting $relation, CurrentRelation $currentRelation): string|_Srting 
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

    public function assembleSQL (string $table1, _Array $relations, int $i, string $columns, string &$sql, bool $iswithCount = false): void 
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

        switch ($type) {
            case $relationsTypes::BELONGSTO:
                if($i == $relations->size - 1 && $columns) $model->relations->BelongsTo->select($columns);
                $sql .= $this->buildBelongsToSQL($model, $currentRelation, $table1, $select, $extraQuery);
            break;

            case  $relationsTypes::HASMANY:
                if(($i == $relations->size - 1 && $columns) || $iswithCount) {
                    $model->relations->HasMany->select($columns);
                }
                $sql .= $this->buildHasManySQL($model, $currentRelation, $table1, $select, $extraQuery);
            break;

            default:
                if(($i == $relations->size - 1 && $columns) || $iswithCount) {
                    $model->relations->ManyToMany->select($columns);
                }
                $sql .= $this->buildManyToManySQL($model, $currentRelation, $table1, $i, $select, $extraQuery);
            break;
        }

        $currentRelation->sql = "SELECT $select , $table.$PK AS mainKey FROM $table $sql WHERE $table.$PK IN ($ids) $extraQuery $orderBy";
    }
   
    private function buildBelongsToSQL (MainModel $model, CurrentRelation $currentRelation, string $table1, string &$select, &$extraQuery): string
    {
        $PK2 = $currentRelation->PK2;
        $FK1 = $currentRelation->FK1;
        $table2 = $currentRelation->table2;

        $select = $model->relations->BelongsTo->query->getSelect($table2);
        $extraQuery = $model->relations->BelongsTo->query->getQuery($table2);
        $model->relations->BelongsTo->query->reset();

        return "INNER JOIN $table2 ON $table1.$FK1 = $table2.$PK2 ";
    }

    private function buildHasManySQL (MainModel $model, CurrentRelation $currentRelation, string $table1, string &$select, &$extraQuery): string
    {
        $PK1 = $currentRelation->PK1;
        $FK2 = $currentRelation->FK2;
        $table2 = $currentRelation->table2;

        $select = $model->relations->HasMany->query->getSelect($table2);
        $extraQuery = $model->relations->HasMany->query->getQuery($table2);
        $model->relations->HasMany->query->reset();

        return "INNER JOIN $table2 ON $table1.$PK1 = $table2.$FK2 ";
    }

    private function buildManyToManySQL (MainModel $model, CurrentRelation $currentRelation, string $table1, int $i, string &$select, &$extraQuery): string
    {
        $PK1 = $currentRelation->PK1;
        $pivotTable = $currentRelation->pivotTable;
        $pivotKey = $currentRelation->pivotKey;
        $relatedKey = $currentRelation->relatedKey;
        $table2 = $currentRelation->table2;
        $PK2 = $currentRelation->PK2;

        $select = $model->relations->ManyToMany->query->getSelect("alias$i");
        if($select != 'COUNT(*) AS count'){
            $select .= ", $pivotTable.$pivotKey AS pivot, $pivotTable.$relatedKey AS related";
        }
        $extraQuery = $model->relations->ManyToMany->query->getQuery();
        $model->relations->ManyToMany->query->reset();

        return "INNER JOIN $pivotTable ON $table1.$PK1 = $pivotTable.$pivotKey 
        INNER JOIN $table2 AS alias$i ON $pivotTable.$relatedKey = alias$i.$PK2 ";
    }
}