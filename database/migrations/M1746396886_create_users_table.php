<?php

use core\Database\migrations\Schema;
use core\Database\migrations\table\Table;

class M1746396886_create_users_table
{
    public function up (): void 
    {
        Schema::create('users', function (Table $table) {            
            $table->id();
            $table->timeStamp();
        });
    } 

    public function down (): void 
    {
        Schema::dropTable('users');
    } 
}