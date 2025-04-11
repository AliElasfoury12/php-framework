<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1744403070_create_test2_table  {
    public function up (): void 
    {
        Schema::create('test2', function (Table $table) 
        {            
            $table->id();
            $table->string('string_forigen');
            $table->foreign('string_forigen')->references('string')->on('test');
            $table->timeStamp();
        });
    } 

    public function down (): void 
    {
        Schema::dropTable('test2');
    } 
}