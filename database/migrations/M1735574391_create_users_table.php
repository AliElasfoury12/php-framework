<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1735574391_create_users_table  {
    public function up () {
        Schema::create('users', function (Table $table) {            
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timeStamp();
        });
    } 

    public function down () {
        Schema::dropTable('users');
    } 
}