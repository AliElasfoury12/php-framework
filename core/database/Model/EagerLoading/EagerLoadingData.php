<?php 

namespace core\database\Model\EagerLoading;

use core\App;
use core\base\_Array;
use core\database\Model\MainModel;
use core\database\Model\Relations\CurrentRelation;
use core\database\Model\Relations\RELATIONSTYPE;

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

            if($result->empty()) $result = $data;
            else {
                if($lastRelation->type === $relationsTypes::BELONGSTO){
                    $result = $this->mregeBelongsToData($lastRelation, $data, $result);
                }else {
                    $result = $this->mregeManyData($lastRelation, $data, $result);
                }
            } 
        }

        return $result;
    }

    private function mregeBelongsToData (CurrentRelation $lastRelation, _Array $data, _Array $result):_Array
    {
        $i = 0;
        foreach ($data as &$value) {
            unset($result[$i]['mainKey']);
            $value[$lastRelation->name] = $result[$i];
            $i++;
            while ($i < $result->size && $value['mainKey'] == $result[$i]['mainKey']) {
                unset($result[$i]['mainKey']);
                $value[$lastRelation->name][] = $result[$i];
                $i++;
            }
        }
        return $data;
    }

    private function mregeManyData (CurrentRelation $lastRelation, _Array $data, _Array $result):_Array
    {
        $i = 0;
        foreach ($data as $key => &$value) {
            $value[$lastRelation->name] = [];
            while ($i < $result->size && $value['mainKey'] == $result[$i]['mainKey']) {
                unset($result[$i]['mainKey']);
                $value[$lastRelation->name][] = $result[$i];
                $i++;
            }
        }
        return $data;
    }
    
    public function injectToModel (MainModel $model, _Array $relations, _Array $result, bool $exsist): void
    {
        $currentRelation = $relations[0];

        $i = 0;
        if(!$exsist) {
            foreach ($model->data as $key => &$value) {
                $value[$currentRelation->name] = [];
                if($i >= $result->size -1) continue;
                unset($result[$key]['mainKey']);
                $value[$currentRelation->name] = $result[$key];
                $i++;
            }
        }else{
            foreach ($model->data as $key => &$value) {
                if($i > $result->size -1) break;
                unset($result[$key]['mainKey']);
                $value[$currentRelation->name][$relations[1]->name] = $result[$key];
                $i++;
            }
        }
    }
}