<?php 

namespace core\database\Model\relations;

use core\App;


trait WithTrait
{
    public static function with(array $relations):array
    {
        $class = new static();

        $model = &App::$app->model;
        
        foreach ($relations as $relation) { 

            if(str_contains($relation, ':')) //posts:id,post
            {
                $model->handleRequestedColumns($relation);
                $relation = $model->relationName;
            }

            if(str_contains($relation, '.')) //posts.comments
            {
                Nested::run($class, $relation);
                continue;
            }

            $model->relationName = $relation;
            $model->handleRelation($class);
        }

        return $model->relationData;
    }

    private function handleRequestedColumns ($relation) 
    {
        $model = App::$app->model;
        preg_match("/([\w\.]+):\s*([\w+,\s*]+)/",$relation, $match);
        $model->relationName = $match[1];//posts
        $model->requestedCoulmns[$model->relationName] = $match[2]; //reqcol[posts] id,post
    }

    /*
    posts
    X posts:id,name
    posts.comments
    X posts.comments:id
     */

    protected function handleRelation ($class): void 
    {
        $model = App::$app->model;

        $relation = call_user_func([$class, $model->relationName]);// users.posts
        $r = $relation;

        switch ($r[0]) {
            case 'HASMANY':
                $model->relationData = HasMany::run($r[1], $r[2], $r[3], $r[4]);
            break;

            case 'BELONGSTO' :
                $model->relationData = BelongsTo::run($r[1], $r[2], $r[3], $r[4]);
            break;

            case 'HASONE':
                $model->relationData = BelongsTo::run($r[1], $r[2], $r[4], $r[3]);
            break;

            case 'MANYTOMANY':
                $model->relationData = ManyToMany::run($r[1], $r[2], $r[3], $r[4]);
            break;
        }
    }    
}
