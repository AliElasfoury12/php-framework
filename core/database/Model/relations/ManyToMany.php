<?php 

namespace Core\Database\Model\Relations;

use core\App;

class ManyToMany {

    public function run (): void //followers, user_id, follower_id 
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL();
        // echo "<pre> <h3> $sql \n\n </h3></pre>";
        $data = $model->fetch($sql);
        $this->inject_data($data);
        $model->query->reset();
    }

    private function prepareSQL ()
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $current_relation = $model->relations->currentRelation;
        $table2 = $current_relation->table2;
        $primaryKey2 = $current_relation->primaryKey;
        $pivotTable = $current_relation->pivotTable;
        $pivotKey = $current_relation->pivotKey;
        $relatedKey = $current_relation->relatedKey;
        
        $extraQuery = $model->relations->extraQuery('alias1');
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];

        $current_relation->FirstSqlPart = 
        "INNER JOIN $pivotTable ON $table1.$primaryKey1 = $pivotTable.$pivotKey
        INNER JOIN $table2 AS alias1 ON alias1.$primaryKey2 = $pivotTable.$relatedKey";
        $current_relation->lastJoin_PK = $primaryKey2;
        $current_relation->lastJoinTable = 'alias1';
       
        return "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $current_relation->FirstSqlPart 
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data (array $data)
    {
        $model = App::$app->model;
        $data_length = count($data);
        $primaryKey1 = $model->primaryKey;

        $i = 0;
        foreach ($model->relations->relationData as &$item) {
            $item[$model->relations->relationName] = [];

            while($i < $data_length && $item[$primaryKey1] == $data[$i]['pivot']){
                unset($data[$i]['pivot']);
                $item[$model->relations->relationName][] = $data[$i];
                $i++;
            }
        }
    }

    public function nested (): void
    {
        $model = App::$app->model;
        $sql = $this->prepareSQL_nested(); 
       // echo "<pre> <h3> $sql \n\n </h3></pre>";
        $data = $model->fetch($sql);
        $this->inject_data_nested($data);
        $model->query->reset();
    }

    private function prepareSQL_nested ()
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey1 = $model->primaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->dataIds;

        $current_relation = $model->relations->currentRelation;
        $table2 = $current_relation->table2;
        $primaryKey2 = $current_relation->primaryKey;
        $pivotTable = $current_relation->pivotTable;
        $relatedKey = $current_relation->relatedKey;
        $pivotKey = $current_relation->pivotKey;
        $first_sql_part = $current_relation->FirstSqlPart;
        $lastTable = $current_relation->lastJoinTable;
        $lastTable_PK = $current_relation->lastJoin_PK;

        $extraQuery = $model->relations->extraQuery('alias2');
        $query = $extraQuery['query'];
        $select = $extraQuery['select'];
 
        return "SELECT $select, $table1.$primaryKey1 AS pivot FROM $table1
        $first_sql_part
        INNER JOIN $pivotTable ON $lastTable.$lastTable_PK = $pivotTable.$pivotKey
        INNER JOIN $table2 AS alias2 ON alias2.$primaryKey2 = $pivotTable.$relatedKey
        WHERE $table1.$primaryKey1 IN ($ids) $query $orderBy";
    }

    private function inject_data_nested (array $data): void
    {
        $model = App::$app->model;
        $dataLength = count($data);

        $primaryKey1 = $model->primaryKey;
        $current_relation = $model->relations->currentRelation; 
        $relation1 = $current_relation->relation1;
        $relation2 = $current_relation->relation2;
        $primaryKey2 = $current_relation->primaryKey;
 
        $i = 0;
        foreach ($model->relations->relationData as &$unit) {
            if(empty($unit[$relation1])) continue;

            if(array_key_exists($primaryKey2, $unit[$relation1])){
                $unit[$relation1][$relation2] = [];
                while($i < $dataLength && $unit[$primaryKey1] == $data[$i]['pivot']){
                    unset($data[$i]['pivot']);
                    $unit[$relation1][$relation2][] = $data[$i];
                    $i++;
                }
            }else {
                foreach ($unit[$relation1] as &$item) {
                   $item[$relation2] = [];
                    while($i < $dataLength && $unit[$primaryKey1] == $data[$i]['pivot']){
                        unset($data[$i]['pivot']);
                        $item[$relation2][] = $data[$i];
                        $i++; 
                    }                   
                }
            }
        }
    }
}


//get followers of user_id = 99

/*
SELECT users.*
FROM users
JOIN followers  
ON followers.follower_id = users.id
WHERE followers.user_id = 99
*/

// the users who user_id = 99 is following

/*SELECT users.*
FROM users
JOIN followers  
ON followers.user_id = users.id
WHERE followers.follower_id = 99
*/