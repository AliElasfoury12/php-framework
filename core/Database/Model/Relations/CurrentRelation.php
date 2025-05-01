<?php 

namespace core\Database\Model\Relations;


class CurrentRelation 
{
    public string $type;
    public string $table1;
    public string $table2;
    public string $PK1;
    public string $PK2;
    public string $FK1;
    public string $FK2;
    public string $pivotTable;
    public string $pivotKey;
    public string $relatedKey;
    public string $name;
    public string $model1;
    public string $model2 ;
    public string $sql;
    public array $withCount;

    public function reset (): void
    {
        $this->type = '';
        $this->table1 = '';
        $this->table2 = '';
        $this->PK1 = '';
        $this->PK2 = '';
        $this->FK1 = '';
        $this->FK2 = '';
        $this->pivotTable = '';
        $this->pivotKey = '';
        $this->relatedKey = '';
        $this->name = '';
        $this->model1 = '';
        $this->model2 = '';
        $this->sql = '';
        $this->withCount = [];
    }
}