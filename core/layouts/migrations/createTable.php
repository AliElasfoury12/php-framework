<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class className
{
    public function up (): void 
    {
        Schema::create('tableName', function (Table $table) {            
            $table->id();
            $table->timeStamp();
        });
    } 

    public function down (): void 
    {
        Schema::dropTable('tableName');
    } 
}