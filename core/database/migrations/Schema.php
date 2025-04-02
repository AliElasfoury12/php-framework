<?php

namespace core\database\migrations;

use Closure;
use core\App;
use core\Command;
use core\database\migrations\table\Table;

class Schema  {
  
    public static function create (string $tableName, Closure $callback): void 
    {
        $table = new Table();
        $table->name = $tableName;
        $callback($table);

        $columns = $table->query->create;
        if (is_array($columns)) {
            $columns = implode(' , ', $columns);
        }
        $sql = "CREATE TABLE IF NOT EXISTS $tableName ( $columns )";
        //echo $sql;
        Command::$do->db->exec($sql);
    }

    public static function table ($tableName, $callback)
    {
        $table = new Table();
        $callback($table);

        $dropColumns = $table->query->drop;
        $addColumns = $table->query->create;

        if($addColumns) {
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
        App::$app->db->exec($sql);
    }

    public static function dropTable ($tableName) {
        $sql ="DROP TABLE IF EXISTS $tableName";
        App::$app->db->exec($sql);
    }
}