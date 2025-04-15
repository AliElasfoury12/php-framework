<?php

namespace core;

use core\base\_Srting;
use core\database\DB;
use core\files\Files;
use core\database\migrations\Migrations;

class Command  
{
    public Migrations $migrations;
    public Files $files;
    public DB $db;
    public static Command $command;

    public function __construct() {
        $this->migrations = new Migrations;
        $this->files = new Files;
        $this->db = new DB;
        self::$command = $this;
    }

    public function handleCommand ($argv): void 
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
                $argv2 = new _Srting($argv[2]);
                if($argv2->contains('create')) 
                    $this->files->createTable( new _Srting($argv[2]));
                elseif($argv2->contains('alter'))
                    $this->files->alterTable( new _Srting($argv[2]));
            break;

            case 'seed':
                $this->seedCommand();
            break;

            case 'model':
                $this->files->createModel($argv[2]);
            break;

            case 'controller':
                $this->files->createController($argv[2]);
            break;
            
            default:
                $this->notFound();
            break;
        }
    
    }

    public function notFound (): void 
    {
        echo "Command Not Found \n";
        exit;
    }

    public function seedCommand (): void 
    {
        echo "Seeding.....................\n";
        echo "Seeding Finshed Successfully\n";
    }

}