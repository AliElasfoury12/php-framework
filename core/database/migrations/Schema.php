<?php

namespace core\database\migrations;

use core\database\DB;
use core\database\migrations\table\Table;

class Schema  {

    public static $tableName ;
    public static function create ($tableName, $callback) 
    {
        self::$tableName = $tableName;
        $table = new Table();
        $callback($table);
        $columns = $table->query['add'];
        if (is_array($columns)) {
            $columns = implode(' , ', $columns);
        }
        $sql = " CREATE TABLE IF NOT EXISTS $tableName ( $columns ) ";
        //echo $sql;
        DB::exec($sql);
    }

    public static function table ($tableName, $callback) {
        $table = new Table();
        $callback($table);

        $dropColumns = $table->query['drop'];
        $addColumns = $table->query['add'];

        if( $addColumns) {
            if(is_string($addColumns)){
                $sql ="ALTER TABLE $tableName ADD COLUMN  $addColumns ;";
            }

            if (is_array($addColumns)) {
                $addColumns = implode(' , ', $addColumns);
                $sql ="ALTER TABLE $tableName ADD COLUMN  $addColumns ;";
            }
        }

        if($dropColumns) {
            if(is_string($dropColumns)){
                $sql ="ALTER TABLE $tableName DROP COLUMN $dropColumns ;";
            }

            if (is_array($dropColumns)) {
                $dropColumns = implode(' , ', $dropColumns);
    
                $sql ="ALTER TABLE $tableName $dropColumns;";
            }
        }
        if($dropColumns && $addColumns ){
            $sql ="ALTER TABLE $tableName ADD COLUMN  $addColumns, $dropColumns ;";
        }
        DB::exec($sql);
    }

    public static function dropTable ($tableName) {
        $sql ="DROP TABLE IF EXISTS $tableName";
        DB::exec($sql);
    }
}