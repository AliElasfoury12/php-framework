<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\Database\Model\MainModel;
use core\Database\Model\Relations\CurrentRelation;

class InjectEagerLoadingDataToModel
{
    private GetEagerLoadingData $GetEagerLoadingData;

    public function __construct()
    {
        $this->GetEagerLoadingData = new GetEagerLoadingData;
    }

    public function injectToModel (_Array $relations, _Array $result, bool $exsist): void
    {
        $model = App::$app->model;
        $currentRelation = $relations[0];

        if(!$exsist) {
           $this->injectNotExsist($currentRelation, $result, $model);
        }else{
            $lastRelation = $relations[1];
           $this->injectExsist($currentRelation,$lastRelation, $result, $model);
        }
        
    }

    private function injectNotExsist (CurrentRelation $currentRelation,_Array $result, MainModel $model): void
    {
        $relationsTypes = $model->relations->Types;
        $PK1 = $model->PrimaryKey;

        if($currentRelation->type === $relationsTypes::BELONGSTO){
            $this->GetEagerLoadingData->mregeBelongsToData($currentRelation, $model->data, $result, $PK1);
        }else if($currentRelation->type === $relationsTypes::HASMANY) {
            $this->GetEagerLoadingData->mregeHasManyData($currentRelation, $model->data, $result, $PK1);
        }else {
            $this->GetEagerLoadingData->mregeManyToManyData($currentRelation, $model->data, $result, $PK1);
        }
    }

    private function injectExsist (CurrentRelation $currentRelation, CurrentRelation $lastRelation,_Array $result, MainModel $model): void
    {
        $relationsTypes = $model->relations->Types;
        $lastRelationName = $lastRelation->name;

        if($lastRelation->type === $relationsTypes::BELONGSTO){
           $this->mergeExsistsBelongsTo($model, $result,$currentRelation->name, $lastRelationName);
        }else if($lastRelation->type === $relationsTypes::HASMANY) {
           $this->mergeExsistsHasMany($model, $result,$currentRelation, $lastRelationName);
        }else {
           $this->mergeExsistsManyToMany($model, $result,$currentRelation, $lastRelationName);
        }
    }

    private function mergeExsistsBelongsTo (MainModel $model, _Array $result, string $currentRelationName, string $lastRelationName): void
    {
        $PK1 = $model->PrimaryKey;

        $i = 0;
        foreach ($model->data as &$value) {
            if($i < $result->size && $value[$PK1] == $result[$i]['mainKey']){
                foreach ($value[$currentRelationName] as $key => $item) {
                    unset($result[$i]['mainKey']);
                    $value[$currentRelationName][$key][$lastRelationName] = $result[$i];
                    $i++;
                }
                
            }
        }
    }

    private function mergeExsistsHasMany (MainModel $model, _Array $result, CurrentRelation $currentRelation, string $lastRelationName): void
    {
        $FK2 = $currentRelation->FK2;
        $PK1 = $model->PrimaryKey;
        $i = 0;
        foreach ($model->data as &$value) {
            foreach ($value[$currentRelation->name] as &$item) {
                if(!empty($value[$currentRelation->name])) $item[$lastRelationName] = [];
                if($i > $result->size - 1) continue;

                while (
                        $i < $result->size &&
                        $value[$PK1] == $result[$i]['mainKey']&&
                        $item[$PK1] == $result[$i][$FK2]
                    ) {
                        unset($result[$i]['mainKey']);
                        $item[$lastRelationName][] = $result[$i];
                        $i++;
                }
            }
        }
    }

    private function mergeExsistsManyToMany (MainModel $model, _Array $result, CurrentRelation $currentRelation, string $lastRelationName): void
    {
        $PK2 = $currentRelation->PK2;
        $PK1 = $model->PrimaryKey;
        $i = 0;

        foreach ($model->data as &$value) {
            foreach ($value[$currentRelation->name] as &$item) {
                if(!empty($value[$currentRelation->name])) $item[$lastRelationName] = [];
                if($i > $result->size - 1) continue;

                while (
                        $i < $result->size  &&
                        $value[$PK1] == $result[$i]['mainKey'] &&
                        $item[$PK1] == $result[$i]['pivot'] &&
                        $result[$i]['related'] == $result[$i][$PK2]

                    ) {
                        unset($result[$i]['mainKey']);
                        unset($result[$i]['pivot']);
                        unset($result[$i]['related']);
                        $item[$lastRelationName][] = $result[$i];
                        $i++;
                }
            }
        }
    }
}