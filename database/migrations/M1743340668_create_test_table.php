<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1743340668_create_test_table  {
    public function up () {
        Schema::create('test', function (Table $table) 
        {          
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('string')->default('user')->primary();
            $table->bool('boolen')->default(false);
            $table->json('_json');
            $table->int('_int_')->nullable();
            $table->timeStamp();
        });
    } 

    public function down () {
        Schema::dropTable('test');
    } 
}