<?php 

namespace core\Database\Model\Relations;

use core\App;
use core\Database\Model\Query\QueryBuilder;

class RelationQueryBuilder extends QueryBuilder
{
      public function with (array $relations): static
    {
        $model = App::$app->model;
        $model->relations->currentRelation->with->set($relations);
        return  $this;
    }

    public function withCount (array $relations):static
    {
        $model = App::$app->model;
        $model->relations->currentRelation->withCount->set($relations);
        return $this;
    }
}