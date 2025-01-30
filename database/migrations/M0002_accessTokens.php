<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M0002_accessTokens 
{
    public function up () {
        Schema::create('accessTokens', function (Table $table) {            
            $table->id();
            $table->bigInt('tokenable_id');
            $table->string('token');
            $table->timesStamp();
        });
    } 

    public function down () {
        Schema::dropTable('accessTokens');
    } 
}