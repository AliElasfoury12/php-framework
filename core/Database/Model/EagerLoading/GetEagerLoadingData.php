<?php 

namespace core\Database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\Database\DB;
use core\Database\Model\Relations\CurrentRelation;

class GetEagerLoadingData
{
    public function fetch (_Array $relations, bool $exsist): _Array
    {
        $result = new _Array;
        $app = App::$app;
        $db = $app->db;
        
        for ($i=$relations->size-1; $i >= 0; $i--) { 
            $currentRelation = $relations[$i];
            if($i == 0 && $exsist) break;

            if($i < $relations->size-1) $lastRelation = $relations[$i+1];
            $data = $db->fetch($currentRelation->sql);

           $this->subEagerLoading($currentRelation, $db, $data);

            if($result->empty()) {
                $result = $data;
                continue;
            }
            
            $this->mergeData($lastRelation,$data, $result);
        }

        return $result;
    }

    private function mergeData (CurrentRelation $lastRelation, _Array $data, _Array &$result): void
    {
        $relationsTypes = App::$app->model->relations->Types;
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

    public function mregeBelongsToData (CurrentRelation $relation, _Array $data, _Array $result, string $PK1 = 'mainKey'):_Array
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

    public function mregeHasManyData (CurrentRelation $relation, _Array $data, _Array $result, string $key = 'mainKey'):_Array
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

    public function mregeManyToManyData (CurrentRelation $lastRelation, _Array $data, _Array $result, string $key = 'mainKey'):_Array
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

    private function subEagerLoading ($currentRelation, DB $db, _Array $data): void 
    {
        if(!$currentRelation->with->empty()){
            $this->mergeWithData($currentRelation->with,$db,$data);
        }

        if(!$currentRelation->withCount->empty()){
           $this->mergeWithCountData($currentRelation->withCount,$db,$data);
        }
    }

    private function mergeWithCountData (_Array $withCountRelations,DB $db,_Array &$data): void 
    {
        foreach ($withCountRelations as $relation) {
            $withCountData = $db->fetch($relation->sql);
            $i = 0;
            foreach ($data as &$value) {
                $value[$relation->name.'Count'] = 0;
                if($i < $withCountData->size - 1 && $value['mainKey'] == $withCountData[$i]['mainKey']){
                    $value[$relation->name.'Count'] = $withCountData[$i]['count'];
                    $i++;
                }
            }
        }
    }

    private function mergeWithData (_Array $withRelations,DB $db,_Array &$data): void 
    {

        foreach ($withRelations as $relation) {
            $withData = $db->fetch($relation->sql);
            $this->subEagerLoading($relation, $db, $withData);
            $this->mergeData($relation,$data, $withData);
        }

    }
}