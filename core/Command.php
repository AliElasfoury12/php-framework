<?php

namespace core;

use core\database\DB;
use core\MainController as Controller;
use core\database\migrations\Migrations;

class Command  
{
    public Migrations $migrations;
    public Controller $controller;
    public DB $db;
    public static Command $command;

    public function __construct() {
        $this->migrations = new Migrations;
        $this->controller = new Controller;
        $this->db = new DB;
        self::$command = $this;
    }

    public function handleCommand ($argv) 
    {
        if($argv[0] != 'bmbo' || empty($argv[1])) {
            $this->notFound();
        }

        switch ($argv[1]) {
            case 'start':
                $port = 8000;
                while (is_resource(@fsockopen('localhost',$port))) {
                   $port++;
                }
                exec("php -S localhost:$port -t public/");
            break;

            case 'migrate':
                $this->migrations->applyMigrations();
            break;

            case 'migration':
                if(str_contains($argv[2], 'create')) 
                    $this->migrations->createTable($argv[2]);
                elseif(str_contains($argv[2], 'alter'))
                    $this->migrations->alterTable($argv[2]);
            break;

            case 'seed':
                $this->seedCommand();
            break;

            case 'model':
                $this->createModel($argv[2]);
            break;

            case 'controller':
                $this->controller->createController($argv[2]);
            break;
            
            default:
                $this->notFound();
            break;
        }
    
    }

    public function notFound () {
        echo "Command Not Found \n" ;
        exit;
    }

    public function seedCommand () {
        echo "Seeding.....................\n";
        echo "Seeding Finshed Successfully\n";
        exit;
    }

    public function createModel ($fileName) {
        $modelFile = file_get_contents(__DIR__.'/layouts/createModel.php');
        $modelFile = preg_replace('/class\s*(.*?)\s*extends/', "class $fileName extends",  $modelFile);
        $exists = file_exists(__DIR__."/../models/$fileName.php");
        if($exists){
            echo "[ models/$fileName ] - file already exsists \n";
            exit;
        }
        file_put_contents(__DIR__."/../app/models/$fileName.php", $modelFile);
        echo "[ models/$fileName ] - Created Successfully \n";
        exit;
    }
}