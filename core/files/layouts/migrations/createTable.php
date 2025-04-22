<?php

use core\Database\migrations\Schema;
use core\Database\migrations\table\Table;

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