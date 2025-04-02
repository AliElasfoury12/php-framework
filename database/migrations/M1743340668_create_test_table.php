<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1743340668_create_test_table  {
    public function up () {
        Schema::create('test', function (Table $table) 
        {            
            $table->id();
            $table->timesStamp();
        });
    } 

    public function down () {
        Schema::dropTable('test');
    } 
}