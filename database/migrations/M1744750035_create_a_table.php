<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1744750035_create_a_table
{
    public function up (): void 
    {
        Schema::create('a', function (Table $table) {            
            $table->id();
            $table->timeStamp();
        });
    } 

    public function down (): void 
    {
        Schema::dropTable('a');
    } 
}