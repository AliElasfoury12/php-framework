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
        if(array_key_exists($relation1, $model->relationData[0])) $exists = true;
       
        if(!$exists) {
            $model->relationName = $relation1; //posts
            $model->handleRelation($class);// get data[posts]
        }

        return $relation1;
    }

    private static function handleSecondRelation ($class, $relation, $relation1): void 
    {
        $model = App::$app->model;

        $class = new $class();
        $relation1Data = call_user_func([$class, $relation1]);

        $relation2 = str_replace("$relation1.", '', $relation); //comments

        if($relation1Data[0] == 'MANYTOMANY') {
            $table1 = App::$app->db->getTable($relation2);
            $class1 = $class;
        }else {
            $table1 = $class::getClassTable();
            $class1 = $model->getClassName($relation1);
        } 

        $primaryKey = $model->getPK($table1);//posts.id

        $class1 = new $class1();// new Post()

        $nestedRelation = call_user_func([$class1, $relation2]); //posts::comments

        switch ($nestedRelation[0]) {
            case 'HASMANY':
                $foreignKey = $model->getFK($relation2, $relation1);// post_id
                HasMany::nested ($relation1, $relation2, $primaryKey, $foreignKey);
            break;

            case 'BELONGSTO':
                $table1 = $nestedRelation[1];
                $table2 = $nestedRelation[2];
                $foreignKey = $model->getFK($table1, $table2);
                BelongsTo::nested($relation1, $relation2, $table2, $primaryKey, $foreignKey);
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