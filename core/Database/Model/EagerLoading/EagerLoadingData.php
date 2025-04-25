<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\Database\Model\MainModel;
use core\Database\Model\Relations\CurrentRelation;
use core\Database\Model\Relations\RELATIONSTYPE;

class EagerLoadingData
{
    public function fetch (_Array $relations, RELATIONSTYPE $relationsTypes, bool $exsist): _Array
    {
        $result = new _Array;
        $db = App::$app->db;
        
        for ($i=$relations->size-1; $i >= 0; $i--) { 
            $currentRelation = $relations[$i];
            if($i == 0 && $exsist) break;
            
            if($i < $relations->size-1) $lastRelation = $relations[$i+1];
            $data = $db->fetch($currentRelation->sql);

            if($result->empty()) {
                $result = $data;
                continue;
            }

            
            switch ($lastRelation->type) {
                case $relationsTypes::BELONGSTO:
                    $result = $this->mregeBelongsToData($lastRelation, $data, $result);
                break;

                case $relationsTypes::HASMANY:
                    $result = $this->mregeHasManyData($lastRelation, $data, $result);
                break;
                
                default:
                    $result = $this->mregeManyToManyData($lastRelation, $data, $result);
                break;
            }
        }

        return $result;
    }

    private function mregeBelongsToData (CurrentRelation $relation, _Array $data, _Array $result, string $PK1 = 'mainKey'):_Array
    {
        $i = 0;
        foreach ($data as &$value) {
            if ($i < $result->size && $value[$PK1] == $result[$i]['mainKey']) {
                unset($result[$i]['mainKey']);
                $value[$relation->name] = $result[$i];
                $i++;
            }
        }

        return $data;
    }

    private function mregeHasManyData (CurrentRelation $relation, _Array $data, _Array $result, string $key = 'mainKey'):_Array
    {
        $PK1 = $relation->PK1;
        $FK2 = $relation->FK2;

        $i = 0;
        foreach ($data as &$value) {
            $value[$relation->name] = [];
            if($i > $result->size - 1) continue;

            while (
                $i < $result->size && 
                $value[$key] == $result[$i]['mainKey'] &&
                $value[$PK1] == $result[$i][$FK2]
                ) {
                    unset($result[$i]['mainKey']);
                    $value[$relation->name][] = $result[$i];
                    $i++;
            }
        }
        return $data;
    }

    private function mregeManyToManyData (CurrentRelation $lastRelation, _Array $data, _Array $result, string $key = 'mainKey'):_Array
    {
        
        $PK1 = $lastRelation->PK1;
        $PK2 = $lastRelation->PK2;

        $i = 0;
        foreach ($data as &$value) {
            $value[$lastRelation->name] = [];

            while (
                $i < $result->size &&
                $value[$key] == $result[$i]['mainKey'] &&
                $value[$PK1] == $result[$i]['pivot'] &&
                $result[$i]['related'] == $result[$i][$PK2]) {
                    unset($result[$i]['mainKey']);
                    unset($result[$i]['pivot']);
                    unset($result[$i]['related']);
                    $value[$lastRelation->name][] = $result[$i];
                    $i++;
            }
        }
        return $data;
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
            $this->mregeBelongsToData($currentRelation, $model->data, $result, $PK1);
        }else if($currentRelation->type === $relationsTypes::HASMANY) {
            $this->mregeHasManyData($currentRelation, $model->data, $result, $PK1);
        }else {
            $this->mregeManyToManyData($currentRelation, $model->data, $result, $PK1);
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
            foreach ($value[$currentRelation->name] as $key => &$item) {
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