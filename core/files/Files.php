<?php 

namespace core\files;

use core\base\_Srting;

class Files
{
    public function createTable (_Srting $fileName): void
    {
        $tableName = '';

        if($fileName->contains('table')) {
            $tableName = $fileName->replace('create_','');
            $tableName = $tableName->replace('_table','');
        }

        $fileName = 'M'.floor(microtime(true))."_$fileName";

        $migrationFile = file_get_contents(__DIR__.'/layouts/migrations/createTable.php');
        $migrationFile = new _Srting($migrationFile);
        $migrationFile = $migrationFile->replace('tableName',$tableName);
        $migrationFile = $migrationFile->replace("className", $fileName);

        file_put_contents(__DIR__."/../../database/migrations/$fileName.php",$migrationFile);
        echo "[ database/migrations/$fileName ] - Created Successfully \n";
    }

    public function alterTable (_Srting $fileName): void
    {
        $tableName = '';

        if($fileName->contains('table')) {
            $tableName = $fileName->replace('alter_','');
            $tableName = $tableName->replace('_table','');
        }

        $fileName = 'M'.floor(microtime(true))."_$fileName";

        $migrationFile = file_get_contents(__DIR__.'/layouts/migrations/alterTable.php');
        $migrationFile = new _Srting($migrationFile);
        $migrationFile = $migrationFile->replace('tableName',$tableName);
        $migrationFile = $migrationFile->replace("className", $fileName);
        
        file_put_contents(__DIR__."/../../database/migrations/$fileName.php",$migrationFile);
        echo "[ database/migrations/$fileName ] - Created Successfully \n";
    }

    public function createModel (string $fileName): void 
    {
        $exists = file_exists(__DIR__."/../../app/models/$fileName.php");
        if($exists){
            echo "[ models/$fileName ] - file already exsists \n";
            exit;
        }

        $modelFile = file_get_contents(__DIR__.'/layouts/createModel.php');
        $modelFile = str_replace('NewModel', $fileName,  $modelFile);
       
        file_put_contents(__DIR__."/../../app/models/$fileName.php", $modelFile);
        echo "[ models/$fileName ] - Created Successfully \n";
    }

    public function createController (string $fileName): void 
    {
        $exists = file_exists(__DIR__."/../../app/controllers/$fileName.php");
        if($exists){
            echo "[ controllers/$fileName ] - file already exsists \n";
            return;
        }

        $controllerFile = file_get_contents(__DIR__.'/layouts/controller.php');
        $controllerFile = str_replace('newController', $fileName,   $controllerFile);
       
        file_put_contents(__DIR__."/../../app/controllers/$fileName.php",  $controllerFile);
        echo "[ controllers/$fileName ] - Created Successfully \n";
    }
}