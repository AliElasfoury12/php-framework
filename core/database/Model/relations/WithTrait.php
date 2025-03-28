<?php 

namespace core\database\Model\relations;

use core\App;

trait WithTrait
{
    public function handleWith(array $relations, string $class): array
    {
        $class = new $class();
        $model = App::$app->model;
        foreach ($relations as $relation) { 

            if(str_contains($relation, ':')) //posts:id,post
            {
                $model->relations->getRequestedColumns($relation);
                $relation = $model->relations->relationName;
            }

            if(str_contains($relation, '.')) //posts.comments
            {
                $model->relations->Nested->run($class::class, $relation);
                $model->requestedCoulmns = '';
                continue;
            }

            $model->select($model->relations->requestedCoulmns);
            call_user_func([$class, $model->relations->relationName]);
            $model->relations->handleRelation();
        }

        return $model->relations->relationData;
    }

    private function getRequestedColumns (string $relation): void 
    {
        $model = App::$app->model;
        $colonPostion = strpos($relation,':');
        $model->relations->relationName = substr($relation, 0, $colonPostion);
        $model->relations->requestedCoulmns = substr($relation, $colonPostion + 1);
    }

    /*
    posts
    X posts:id,name
    posts.comments
    X posts.comments:id
     */

    public function handleRelation (): void
    {
        $model = App::$app->model;
        $RelationsTypes = $model->relations->relationTypes;

        match ($model->relations->currentRelation->type) {
            $RelationsTypes::HASMANY  =>  $model->relations->HasMany->run(),
            $RelationsTypes::BELONGSTO =>  $model->relations->BelongsTo->run(),
            $RelationsTypes::HASONE =>  $model->relations->BelongsTo->run(),
            $RelationsTypes::MANYTOMANY => $model->relations->ManyToMany->run()
        };
    }    
}