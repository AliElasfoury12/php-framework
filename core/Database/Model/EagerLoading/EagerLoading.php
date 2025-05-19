<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\base\_String;

class EagerLoading
{
    private EagerLoadingSQLBuilder $EagerLoadingSQLBuilder;
    private GetEagerLoadingData $EagerLoadingData;
    private InjectEagerLoadingDataToModel $InjectEagerLoadingDataToModel;
    
    
    public function __construct()
    {
        $this->EagerLoadingSQLBuilder = new EagerLoadingSQLBuilder;
        $this->EagerLoadingData = new GetEagerLoadingData;
        $this->InjectEagerLoadingDataToModel = new InjectEagerLoadingDataToModel;
    }

    public function run (string $class, _String $relation): void 
    {
        $model = App::$app->model;
        $relations = $relation->explode('.');

        $this->EagerLoadingSQLBuilder->buildSQL($relations, $class);

        $exsist = $model->data[0]->hasKey($relations[0]->name);
        $result = $this->EagerLoadingData->fetch($relations, $exsist);
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
            $relation = new _String($model->relations->with[$i]);           
            $this->run($class::class, $relation);
        }
       
        return $model->data;
    }
 
    public function handleWithCount (): void 
    {
        $model = App::$app->model;
        $currentRelation = $model->relations->currentRelation;
        $class = new $model->class;
        $relationsTypes = $model->relations->Types;
        $withCountRelations = $model->relations->withCount;
        $sql = '';

        foreach ($withCountRelations as $key => $relationName) {
            call_user_func([$class,$relationName]);
            $table1 = $currentRelation->table1;
            $PK1 = $currentRelation->PK1;

            if($currentRelation->type  == $relationsTypes::HASMANY){
                $model->relations->HasMany->groupBy("$table1.$PK1") ;
            }else $model->relations->ManyToMany->groupBy("$table1.$PK1");
         

            $sql = $this->EagerLoadingSQLBuilder
            ->assembleSQL(
                $withCountRelations,
                $key,
                "",
                $sql,
                true);


            $relation_data = App::$app->db->fetch($sql);
            $this->injectWithCountDataToMode($relationName,$relation_data,$PK1);
            $withCountRelations[$key] = clone $currentRelation;
            $sql = '';
        }
    }

    private function injectWithCountDataToMode (string $relationName, _Array $relation_data, string $PK1): void 
    {
        $model = App::$app->model;

        $i = 0;
        foreach ($model->data as &$item) {
            $item[$relationName.'Count'] = 0;

            if($i < $relation_data->size && $item[$PK1] === $relation_data[$i]['mainKey'] ){
                $item[$relationName.'Count'] = $relation_data[$i]['count'];
                $i++;
            }               
        }
    }
}