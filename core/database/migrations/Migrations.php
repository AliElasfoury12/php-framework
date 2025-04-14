<?php

namespace core\database\migrations;

use core\base\_Array;
use PDO;
use core\database\DB;

class Migrations
{
    public DB $db;
    public function __construct()
    {
        $this->db = new DB();
    }
   
    public function applyMigrations (): void
    {
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = new _Array();

        $files = scandir( __DIR__."/../../../database/migrations");
        $toApplyMigrtions = $appliedMigrations->diff($files);

        foreach ($toApplyMigrtions as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }

            require_once __DIR__."/../../../database/migrations/$migration";

            $className = pathinfo($migration, PATHINFO_FILENAME);

            $instance = new $className();
            $this->log("Applying migration $migration");
            $instance->up();
            $this->log("Applied migration $migration");
            $newMigrations[] = $migration;
        }

        if ($newMigrations->size) {
            $this->saveMigrations($newMigrations);
        }else {
           $this->log('All Migrations Are Applied');
        }
    }

    public function getAppliedMigrations (): _Array
    {
        if($this->db->tableIsExsists('migrations')){
            return $this->db->fetch("SELECT migration FROM migrations", PDO::FETCH_COLUMN);
        };

        return new _Array();
    }

    private function saveMigrations (_Array $migrations): void
    {
        $migrations->map(fn ($m) => "('$m')");
        $str = $migrations->implode(',');
        $this->db->exec("INSERT INTO migrations ( migration ) VALUES $str");
    }

    public function log (string $message): void
    {
        echo '['.date('Y-m-d H:i:s').'] - '.$message.PHP_EOL;
    }

    public function createTable (string $fileName): void
    {
        $migrationFile = file_get_contents(__DIR__.'/../../layouts/migrations/createTable.php');
        $tableName = '';

        if(str_contains($fileName, 'table')) {
            $tableName = str_replace('create_','',$fileName);
            $tableName = str_replace('_table','',$tableName);
        }

        $migrationFile = str_replace('tableName',$tableName, $migrationFile);
        $fileName = 'M'.floor(microtime(true))."_$fileName";
        $migrationFile = preg_replace('/class\s*(.*?)\s*{/', "class $fileName  {",  $migrationFile);
        
        file_put_contents(__DIR__."/../../../database/migrations/$fileName.php",$migrationFile);
        echo "[ database/migrations/$fileName ] - Created Successfully \n";
    }

    public function alterTable (string $fileName): void
    {
        $migrationFile = file_get_contents(__DIR__.'/../../layouts/migrations/alterTable.php');
        $tableName = '';

        if(str_contains($fileName, 'table')) {
            $tableName = str_replace('alter_','',$fileName);
            $tableName = str_replace('_table','',$tableName);
        }

        $migrationFile = str_replace('tableName',$tableName, $migrationFile);
        $fileName = 'M'.floor(microtime(true))."_$fileName";
        $migrationFile = preg_replace('/class\s*(.*?)\s*{/', "class $fileName  {",  $migrationFile);
        
        file_put_contents(__DIR__."/../../../database/migrations/$fileName.php",$migrationFile);
        echo "[ database/migrations/$fileName ] - Created Successfully \n";
    }

}