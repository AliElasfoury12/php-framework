<?php

namespace core\database;

use core\App;
use core\base\_Array;
use PDO;

class DB 
{
    public PDO $pdo;

    public function __construct () 
    {
        $connection = $_ENV['DB_CONNECTION'];
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbName = $_ENV['DB_DATABASE'];
        $dsn = "$connection:host=$host;port=$port;dbname=$dbName;";
        $user = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
     
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

   
    public function exec (string $sql): bool|\PDOStatement
    {
        //echo "$sql <br>";
        $statment = $this->pdo->prepare($sql);
        try {
            $statment->execute();
        } catch (\Throwable $th) {
            App::displayError($th);
            exit;
        }
        return $statment;
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

    public function tableIsExsists (string $class): bool 
    {   
        $sql = "SHOW TABLES LIKE '$class'";
        return !$this->fetch($sql)->empty();
    }

    public function getTable (string $class): string 
    {
        $table = '';
        $classLowerCase = strtolower($class); 

        if($this->tableIsExsists($classLowerCase.'s')) $table = $classLowerCase.'s';
        else if($this->tableIsExsists($classLowerCase)) $table = $classLowerCase;
        else{
            $classSnakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));

            if($this->tableIsExsists($classSnakeCase.'s')) $table = $classSnakeCase.'s';
            else if($this->tableIsExsists($classSnakeCase)) $table = $classSnakeCase;
        }
        return $table;
    }

    public function fetch (string $sql, int $type = PDO::FETCH_ASSOC): _Array
    {
       // echo "$sql <br><br>";
        return new _Array($this->pdo->query($sql)->fetchAll($type));
    }

    public function getPK (string $table): mixed 
    {
        $sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        //echo "$sql <br>";
        $result = App::$app->db->fetch($sql);
        return $result[0]["Column_name"];
    }

    public function getFK (string $table1, string $table2): mixed
    {
        $table2 = rtrim($table2, 's');
        $sql = "SHOW KEYS FROM $table1 WHERE Key_name Like '%$table2%'";
        //echo "$sql <br>";
        $result = App::$app->db->fetch($sql);
        return $result[0]["Column_name"];
    }
}