<?php

namespace core\database;

use core\App;
use PDO;

class DB 
{
    public static PDO $pdo;

    public function __construct () 
    {
        $connection = $_ENV['DB_CONNECTION'];
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbName = $_ENV['DB_DATABASE'];
        $dsn = "$connection:host=$host;port=$port;dbname=$dbName;";
        $user = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
     
        self::$pdo = new PDO($dsn, $user, $password);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

   
    public function exec (string $sql): bool|\PDOStatement
    {
        //echo "$sql <br>";
        $statment = self::$pdo->prepare($sql);
        try {
            $statment->execute();
        } catch (\Throwable $th) {
            App::displayError($th);
            exit;
        }
        return $statment;
    }

     public function fetch (string $sql, string $type = ''){
        switch ($type) {
            case 'obj':
                $type = PDO::FETCH_OBJ;
            break;
            case 'col':
                $type = PDO::FETCH_COLUMN;
            break;
            
            default:
               $type = PDO::FETCH_ASSOC;
        }

        $statment = $this->exec ($sql);
        return $statment->fetchAll($type);
    }

    public function insert ($table, $columns, $values)
    {
        if(is_array($columns)){
            $columns = implode(', ',  $columns);
        }

        if(is_array($values)){
            $values = array_map(fn($v) => "'$v'", $values);
            $values = implode(', ', $values);
        }
        
        $sql = "INSERT INTO $table ( $columns ) VALUES ( $values ) ";
        //echo $sql;
        return $this->exec($sql); 
    }

    public function tableIsExsists (string $class): mixed 
    {   
        $sql = "show TABLES LIKE '$class'";
        return $this->fetch($sql);
    }

    public function getTable (string $class) 
    {
        $table = '';
        $classLowerCase = strtolower($class); 
        if($this->tableIsExsists($classLowerCase.'s')) $table = $classLowerCase.'s';
        else if($this->tableIsExsists($classLowerCase))  $table = $classLowerCase;
        else{
            $classSnakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));

            if($this->tableIsExsists($classSnakeCase.'s')) $table = $classSnakeCase.'s';
            else if($this->tableIsExsists($classSnakeCase)) $table = $classSnakeCase;
        } 
        return $table;
    }
}