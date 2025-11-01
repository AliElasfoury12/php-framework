<?php

use core\Database\migrations\Schema;
use core\Database\migrations\table\Table;

class className
{
    public function up (): void 
    {
        Schema::table('tableName', function (Table $table) {            
          

            
        });
    } 

    public function down (): void 
    {
        Schema::table('tableName', function (Table $table) {            
          
        });
    } 
}