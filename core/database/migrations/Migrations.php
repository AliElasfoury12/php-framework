<?php

namespace core\database\migrations;

use PDO;
use core\database\DB;

class Migrations extends DB
{
   
    public function applyMigrations ()
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

    public function getAppliedMigrations ()
    {
       try {
            $statment = self::exec("SELECT  migration FROM migrations");
            return $statment->fetchAll(PDO::FETCH_COLUMN) ;
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function saveMigrations (array $migrations)
    {
        $migrations = array_map(fn ($m) => "('$m')",$migrations);
        $str = implode(',', $migrations);
        self::exec("INSERT INTO migrations ( migration ) VALUES $str");
    }

    public function log ($message)
    {
        echo '['.date('Y-m-d H:i:s').'] - '.$message.PHP_EOL;
    }

    public function createTable ($fileName)
    {
        $tableName = str_replace('create_','',$fileName);
        $tableName = str_replace('_table','',$tableName);
        $migrationFile = file_get_contents(__DIR__.'/../../layouts/createTable.php');
        $migrationFile = str_replace('tableName',$tableName, $migrationFile);
        $fileName = 'M'.floor(microtime(true))."_$fileName";
        $migrationFile = preg_replace('/class\s*(.*?)\s*{/', "class $fileName  {",  $migrationFile);
        
        file_put_contents(__DIR__."/../../../database/migrations/$fileName.php",$migrationFile);
        echo "[ database/migrations/$fileName ] - Created Successfully \n";
        exit;
    }

}