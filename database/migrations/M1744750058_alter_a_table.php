<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1744750058_alter_a_table
{
    public function up (): void 
    {
        Schema::table('a', function (Table $table) {            
          

            
        });
    } 

    public function down (): void 
    {
        Schema::table('a', function (Table $table) {            
          
        });
    } 
}