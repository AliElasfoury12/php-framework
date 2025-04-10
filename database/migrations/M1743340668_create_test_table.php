<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1743340668_create_test_table  {
    public function up () {
        Schema::create('test', function (Table $table) 
        {            
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('string')->default('user');
            $table->bool('boolen')->default(false);
            $table->bigInt('big_int')->default(5);
            $table->int('_int_');
            $table->timesStamp();
        });
    } 

    public function down () {
        Schema::dropTable('test');
    } 
}