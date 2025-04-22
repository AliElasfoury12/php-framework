<?php 

namespace core\database\Model\relations;

use core\App;
use core\base\_Array;

class BelongsTo extends RelationQueryBuilder
{

    public function run (): void
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL();
        //echo "$sql <br>"; 
        $data = App::$app->db->fetch($sql);
        foreach ($model->data as $key => &$item) {
            $item[$model->relations->currentRelation->name] = $data[$key];
        }

        $this->query->reset();
        $model->relations->currentRelation->columns = '';
    }

    private function prepareSQL (): string 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;
        $currentRelation = $model->relations->currentRelation;

        $table2 = $currentRelation->table2;
        $foreignKey = $currentRelation->FK1;
        $primaryKey2 = $currentRelation->PK2;

        if($currentRelation->columns) $this->select($currentRelation->columns);
        $select = $this->query->getSelect($table2);
        $query = $this->query->getQuery($table2);

        $currentRelation->FirstSqlPart = 
        "INNER JOIN $table2  ON $table2.$primaryKey2 = $table1.$foreignKey";
        $currentRelation->lastJoin_PK = $primaryKey2;
        $currentRelation->lastJoinTable = $table2;
      
        return "SELECT $select FROM $table1 
        INNER JOIN $table2 ON $table2.$primaryKey2 = $table1.$foreignKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

}