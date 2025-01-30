<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class className
{
    public function up () {
        Schema::table('tableName', function (Table $table) {            
          

            
        });
    } 

    public function down () {
        Schema::table('tableName', function (Table $table) {            
          
        });
    } 
}