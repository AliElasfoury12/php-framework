<?php

use core\database\migrations\Schema;
use core\database\migrations\table\Table;

class M1744320410_alter_test_table  {
    public function up () 
    {
        Schema::table('test', function (Table $table) {            
            $table->text('_text')->unique()->after('user_id');
            $table->bigInt('big_int')->unsigned()->default(5);
            $table->dropColumn('_int_');
            $table->dropColumn('boolen');
        });
    } 

    public function down () 
    {
        Schema::table('test', function (Table $table) {            
          
        });
    } 
}