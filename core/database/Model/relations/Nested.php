<?php 

namespace core\database\Model\relations;

use core\App;

class Nested  {
    private $relation1; 
    private $relation2; 

    public function run (string $class, string $relation): void 
    {
        $this->handleFirstRelation($class, $relation); 
        $this->handleSecondRelation($class);
    }

    private function handleFirstRelation (string $class, string $relation)
    {
        $dotPositon = strpos($relation,'.');
        $this->relation1 = substr($relation, 0, $dotPositon);
        $this->relation2 = substr($relation, $dotPositon + 1);

        $model = App::$app->model;
        $model->relations->currentRelation->name = $this->relation1;
        if($model->data && array_key_exists($model->relations->currentRelation->name, $model->data[0])) {
            $model->query->reset();
            return;
        }

        $class = new $class;
        call_user_func([$class, $this->relation1]);
        $model->relations->handleRelation();
    }

    private function handleSecondRelation (string $class): void 
    {
        $model = App::$app->model;
        $class1 = $model->getClassName($this->relation1);
        if(!class_exists($class1)){
            $class1 = $class;
        }
        $class1 = new $class1();

        call_user_func([$class1, $this->relation2]); 

        $model->relations->currentRelation->relation1 = $this->relation1;
        $model->relations->currentRelation->relation2 = $this->relation2;

        if($model->relations->currentRelation->columns) $model->select($model->relations->currentRelation->columns);
        $types = $model->relations->relationTypes;

        match ($model->relations->currentRelation->type) {
            $types::HASMANY => $model->relations->HasMany->nested(),
            $types::BELONGSTO => $model->relations->BelongsTo->nested(),
            $types::MANYTOMANY => $model->relations->ManyToMany->nested()
        };
    }
}