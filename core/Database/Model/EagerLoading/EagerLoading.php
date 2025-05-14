<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\base\_Srting;

class EagerLoading
{
    private EagerLoadingSQLBuilder $BuildEagerLoadingSQL;
    private GetEagerLoadingData $EagerLoadingData;
    private InjectEagerLoadingDataToModel $InjectEagerLoadingDataToModel;
    
    public function __construct()
    {
        $this->BuildEagerLoadingSQL = new EagerLoadingSQLBuilder;
        $this->EagerLoadingData = new GetEagerLoadingData;
        $this->InjectEagerLoadingDataToModel = new InjectEagerLoadingDataToModel;
    }

    public function run (string $class, _Srting $relation): void 
    {
        $model = App::$app->model;
        $relations = $relation->explode('.');
        $relationsTypes = $model->relations->Types;

        $this->BuildEagerLoadingSQL->buildSQL($relations, $class, $relationsTypes);
        $exsist = $model->data[0]->hasKey($relations[0]->name);
        $result = $this->EagerLoadingData->fetch( $relations, $relationsTypes, $exsist);
        if($model->data->empty()) {
            $model->data = $result;
            return;
        }
        
        $this->InjectEagerLoadingDataToModel
        ->injectToModel($relations, $result, $exsist);
    }

    public function handleWith(string $class): _Array
    {
        $class = new $class();
        $model = App::$app->model;

        for ($i=0; $i < $model->relations->with->size; $i++) { 
            $relation = new _Srting($model->relations->with[$i]);           
            $this->run($class::class, $relation);
        }
       
        return $model->data;
    }
 
    public function handleWithCount (): void 
    {
        $model = App::$app->model;
        $table1 = $model->table;
        $primaryKey = $model->PrimaryKey;
        $orderBy = $model->orderBy;
        $ids = $model->ids;

        foreach ($model->relations->withCount as $relationName) {
            //call_user_func($relationName);

            $forigenKey = App::$app->db->getFK($relationName, $table1);

            $sql = "SELECT COUNT(*) AS count, $table1.$primaryKey AS pivot FROM $table1 
            INNER JOIN $relationName ON $table1.$primaryKey = $relationName.$forigenKey
            WHERE $table1.$primaryKey IN ($ids)
            GROUP BY $table1.$primaryKey $orderBy";

            //echo "$sql <br> <br>";

            $data = App::$app->db->fetch($sql);

            $i = 0;
            foreach ($model->data as &$item) {
                $item[$relationName.'Count'] = 0;

                if($i < $data->size && $item[$primaryKey] === $data[$i]['pivot'] ){
                    $item[$relationName.'Count'] = $data[$i]['count'];
                    $i++;
                }               
            }
        }
    }
}