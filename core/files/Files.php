<?php 

namespace core\files;

use core\base\_String;

class Files
{
    private const LAYOUT_PATH = __DIR__.'/layouts';
    public function createTable (_String $fileName): void
    {
        $tableName = '';

        if($fileName->contains('table')) {
            $tableName = $fileName->replace('create_','');
            $tableName = $tableName->replace('_table','');
        }

        $fileName = 'M'.floor(microtime(true))."_$fileName";

        $migrationFile = file_get_contents(self::LAYOUT_PATH.'/migrations/createTable.php');
        $migrationFile = new _String($migrationFile);
        $migrationFile = $migrationFile->replace('tableName',$tableName);
        $migrationFile = $migrationFile->replace("className", $fileName);

        file_put_contents(__DIR__."/../../database/migrations/$fileName.php",$migrationFile);
        echo "[ database/migrations/$fileName ] - Created Successfully \n";
    }

    public function alterTable (_String $fileName): void
    {
        $tableName = '';

        if($fileName->contains('table')) {
            $tableName = $fileName->replace('alter_','');
            $tableName = $tableName->replace('_table','');
        }

        $fileName = 'M'.floor(microtime(true))."_$fileName";

        $migrationFile = file_get_contents(self::LAYOUT_PATH.'/migrations/alterTable.php');
        $migrationFile = new _String($migrationFile);
        $migrationFile = $migrationFile->replace('tableName',$tableName);
        $migrationFile = $migrationFile->replace("className", $fileName);
        
        file_put_contents(__DIR__."/../../database/migrations/$fileName.php",$migrationFile);
        echo "[ database/migrations/$fileName ] - Created Successfully \n";
    }

    public function createModel (string $fileName): void 
    {
        $path = __DIR__."/../../app/models/$fileName.php";
        $exists = file_exists($path);
        if($exists){
            echo "[ models/$fileName ] - file already exsists \n";
            exit;
        }

        $modelFile = file_get_contents(self::LAYOUT_PATH.'/createModel.php');
        $modelFile = str_replace('NewModel', $fileName,  $modelFile);
       
        file_put_contents($path, $modelFile);
        echo "[ models/$fileName ] - Created Successfully \n";
    }

    public function createController (string $fileName): void 
    {
        $path = __DIR__."/../../app/controllers/$fileName.php";
        $exists = file_exists($path);
        if($exists){
            echo "[ controllers/$fileName ] - file already exsists \n";
            return;
        }

        $controllerFile = file_get_contents(self::LAYOUT_PATH.'/controller.php');
        $controllerFile = str_replace('newController',$fileName,$controllerFile);
       
        file_put_contents( $path,  $controllerFile);
        echo "[ controllers/$fileName ] - Created Successfully \n";
    }
}