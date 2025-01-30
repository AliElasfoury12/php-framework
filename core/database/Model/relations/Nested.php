<?php 

namespace core\database\Model\relations;

use core\App;

class Nested extends Relations {
   
    public static function run ($class, $relation) 
    {
        //user -> posts.comments
        $relation1 = self::handleFirstRelation($class, $relation);
        self::handleSecondRelation($class, $relation, $relation1);
    }

    private static function handleFirstRelation ($class, $relation) 
    {
        $model = App::$app->model;

        preg_match('/(\w+)\./',$relation, $match);
        $relation1 = $match[1];//posts
        
        $exists = false;
        foreach ($model->relationData as $items) {
            if(array_key_exists($relation1, $items)) //data[posts]
            {
                $exists = true;
                break;
            }
        }
 
        if(!$exists) {
            $model->relationName = $relation1; //posts
            $model->handleRelation($class);// get data[posts]
        }

        return $relation1;
    }

    private static function handleSecondRelation ($class, $relation, $relation1): void 
    {
        $model = App::$app->model;

        $table1 = $class::getTableName();
        $primaryKey = $model->getPK($table1);//posts.id

        $relation2 = str_replace("$relation1.", '', $relation); //comments
        $class = self::getClassName($relation1);
        $class = new $class();// new Post()

        $nestedRelation = call_user_func([$class, $relation2]); //posts::comments

        switch ($nestedRelation[0]) {
            case 'HASMANY':
                $index = rtrim($relation1, 's');//posts => post
                $foreignKey = $model->getFK($relation2, $index);// post_id
                HasMany::nested ($relation1, $relation2, $primaryKey, $foreignKey);
            break;

            case 'BELONGSTO':
                var_dump($nestedRelation);
                $class = self::getClassName($relation1);
                $table1 = $class::getTableName();

                $class2 = self::getClassName($relation2);
                $table2 = $class2::getTableName();

                $index = rtrim($table2, 's');
                $foreignKey = $model->getFK($table1, $index);//
                BelongsTo::run($table1, $table2, $foreignKey, $primaryKey);
            break;

            case 'MANYTOMANY':
                $table1 = $nestedRelation[1];
                $table2 = $nestedRelation[2];
                $pivotKey = $nestedRelation[3];
                $relatedKey = $nestedRelation[4];
    
                ManyToMany::nested($table1,
                                       $table2, 
                                       $relation1, 
                                       $relation2, 
                                       $primaryKey, 
                                       $relatedKey, 
                                       $pivotKey);
            break;
        }
    }

    private static function getClassName ($relation) 
    {
        $class = ucfirst($relation);//Posts
        $class = trim($class, 's');//Post
        return str_replace('/', '', "app\models\/$class");// app\models\Post
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