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
        $model->relationName = $this->relation1;
        if(array_key_exists($model->relationName, $model->relationData[0])) {
            $model->query = [];
            return;
        }

        $class = new $class;
        call_user_func([$class, $this->relation1]);
        $model->handleRelation();
    }

    private function handleSecondRelation (string $class): void 
    {
        $model = App::$app->model;
        $class1 = $model->getClassName($this->relation1);
        if(!class_exists($class1)){
            $class1 = $class;
        }
        $class1 = new $class1();

        call_user_func([$class1, $this->relation2]); //posts::comments

        $model->currentRelation->relation1 = $this->relation1;
        $model->currentRelation->relation2 = $this->relation2;

        if($model->requestedCoulmns) $model->select($model->requestedCoulmns);
        $types = $model->relationTypes;

        switch ($model->currentRelation->type) {
            case $types::HASMANY:
                $model->HasMany->nested();
            break;

            case $types::BELONGSTO:
                $model->BelongsTo->nested();
            break;

            case $types::MANYTOMANY:
                $model->ManyToMany->nested();
            break;
        }
    }
}

/*
posts = [
    [
        id => 1,
        post=> post1,
        user => [
            id => 1,
            name => user1,
            followers => [
                id => 3,
                name => user3
            ]
        ]
    ],[
        id => 2,
        post=> post2,
        user => [
            id => 2,
            name => user2,
            followers => [
                id => 4,
                name => user4
            ]
        ]
    ]
]

users = [
    [
        id => 1,
        name => user1,
        posts => [
            [
                id => 1,
                post=> post1,
            ],[
                id => 2,
                post=> post2
            ]
        ]
    ],[
        id => 2,
        name => user2,
        posts => [
            [
                id => 3,
                post=> post3
            ]
        ]
    ]
]
*/