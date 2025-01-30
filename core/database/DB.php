<?php

namespace core\database;

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

   
    public static function exec ($sql){
        //echo "$sql <br>";
        $statment = self::$pdo->prepare($sql);
        $statment->execute();
        return $statment;
    }

     public function fetch ($sql, $type = ''){
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

        $statment = self::exec ($sql);
        return $statment->fetchAll($type);
    }

    public static function insert ($table, $columns, $values)
    {
        if(is_array($columns)){
            $columns = implode(', ',  $columns);
        }

        if(is_array($values)){
            $values = array_map(fn($v) => "'$v'", $values);
            $values = implode(', ', $values);
        }
        
        $sql = "INSERT INTO $table ( $columns ) VALUES ( $values ) ";
        echo $sql;
        return DB::exec($sql); 
    }

}