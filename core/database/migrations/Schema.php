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
        echo "\n $sql \n\n";
        Command::$do->db->exec($sql);
    }

    public static function table ($tableName, $callback): void
    {
        $table = new Table();
        $callback($table);

        $dropColumns = $table->query->drop;
        $addColumns = $table->query->create;

        if($addColumns) {
            $addColumns = array_map(fn ($column) => " ADD COLUMN $column", $addColumns);
            $addColumns = implode(' , ', $addColumns);
        }

        if($dropColumns) {
            $dropColumns = array_map(fn ($column) => "DROP COLUMN $column", $dropColumns);
            $dropColumns = implode(' , ', $dropColumns);
        }

        if($dropColumns && $addColumns ){
            $sql ="ALTER TABLE $tableName $addColumns, $dropColumns ";
        }else if ($addColumns){
            $sql ="ALTER TABLE $tableName $addColumns";
        }else{
            $sql ="ALTER TABLE $tableName $dropColumns ";
        }
        
        echo "\n $sql \n\n";
        Command::$do->db->exec($sql);
    }

    public static function dropTable ($tableName) {
        $sql ="DROP TABLE IF EXISTS $tableName";
        echo "\n $sql \n\n";
        Command::$do->db->exec($sql);
    }
}