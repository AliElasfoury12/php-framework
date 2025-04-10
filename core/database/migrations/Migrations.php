<?php

namespace core\database\migrations;

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

        $newMigrations = [];

        $files = scandir( __DIR__."/../../../database/migrations");
        $toApplyMigrtions = array_diff($files, $appliedMigrations);

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

        if (count($newMigrations)) {
            $this->saveMigrations($newMigrations);
        }else {
           $this->log('All Migrations Are Applied');
        }
    }

    public function getAppliedMigrations (): array
    {
       try {
            $statment = $this->db->exec("SELECT  migration FROM migrations");
            return $statment->fetchAll(PDO::FETCH_COLUMN) ;
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function saveMigrations (array $migrations): void
    {
        $migrations = array_map(fn ($m) => "('$m')",$migrations);
        $str = implode(',', $migrations);
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