<?php 

namespace core\database\Model\relations;

use core\App;

class Nested extends Relations {
    private static $relation1; 
    private static $relation2; 

    public static function run (string $class, string $relation): void 
    {
        $sql = "SELECT users.id, users.name from posts 
        INNER JOIN shared_posts ON posts.id = shared_posts.shared_post_id
        INNER JOIN posts as shared ON shared_posts.shared_post_id = shared.id
        INNER JOIN users ON shared.user_id = users.id";

        //user -> posts.comments
        $firstSql = self::handleFirstRelation($class, $relation); 
        self::handleSecondRelation($class, $firstSql);
    }

    private static function handleFirstRelation (string $class, string $relation): string 
    {
        $dotPositon = strpos($relation,'.');
        self::$relation1 = substr($relation, 0, $dotPositon);
        self::$relation2 = substr($relation, $dotPositon + 1);

        $model = App::$app->model;
        $model->relationName = self::$relation1; //posts
        $class = new $class;
        call_user_func([$class, self::$relation1]);
        return $model->handleRelation();
    }

    private static function handleSecondRelation (string $class, string $firstSql): void 
    {
        $model = App::$app->model;
        $class1 = $model->getClassName(self::$relation1);
        if(!class_exists($class1)){
            $class1 = $class;
        }
        $table1 = $model->getClassTable($class1);
        $class1 = new $class1();// new Post()

        if(!method_exists($class1, self::$relation2) ) {
            $table1 = $model->table;
        }

        call_user_func([$class1, self::$relation2]); //posts::comments

        $model->currentRelation->relation1 = self::$relation1;
        $model->currentRelation->relation2 = self::$relation2;

        if($model->requestedCoulmns) $model->select($model->requestedCoulmns);
        $types = $model->relationTypes;

        switch ($model->currentRelation->type) {
            case $types::HASMANY:
                HasMany::nested($firstSql);
            break;

            case $types::BELONGSTO:
                BelongsTo::nested($firstSql);
            break;

            case $types::MANYTOMANY:
                $model->currentRelation->table1 = $table1;
                ManyToMany::nested($firstSql);
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