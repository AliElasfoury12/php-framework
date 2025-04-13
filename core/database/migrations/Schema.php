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

        $columns = $table->query->create->implode(',');
        $sql = "CREATE TABLE IF NOT EXISTS $tableName ( $columns )";
        echo "\n $sql \n\n";
        Command::$command->db->exec($sql);
    }

    public static function table ($tableName, $callback): void
    {
        $table = new Table();
        $callback($table);

        $dropColumns = $table->query->drop;
        $addColumns = $table->query->create;

        if($addColumns) {
            $addColumns->map(fn ($column) => " ADD COLUMN $column");
            $addColumns = $addColumns->implode(',');
        }

        if($dropColumns) $dropColumns = $dropColumns->implode(',');

        if($dropColumns && $addColumns ) $columns = "$addColumns, $dropColumns";
        elseif ($addColumns) $columns = $addColumns;
        else $columns = $dropColumns;

        $sql ="ALTER TABLE $tableName $columns";

        echo "\n $sql \n\n";
        Command::$command->db->exec($sql);
    }

    public static function dropTable ($tableName) {
        $sql ="DROP TABLE IF EXISTS $tableName";
        echo "\n $sql \n\n";
        Command::$command->db->exec($sql);
    }
}