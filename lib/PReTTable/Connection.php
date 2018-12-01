<?php

namespace PReTTable;

use PDO;

class Connection {
    
    private $environment;
    
    private $connection;
    
    private static $data;

    function __construct() {
        $this->environment = getenv('PReTTable_CONNECTION_ENV');
        
        if (!isset($this->environment)) {
            $this->environment = 'development';
        }
    }
    
    function establishConnection($database) {
        $data = self::$data[$database][$this->environment];
        
        $adapter = $data['adapter'];
        $host = $data['host'];
        
        $username = null;
        if (array_key_exists('username', $data)) {
            $username = $data['username'];
        }
        
        $password = null;
        if (array_key_exists('password', $data)) {
            $password = $data['password'];
        }
        
        $dsn = "$adapter:=$host;dbname=$database";
        $this->connection = new PDO($dsn, $username, $password);
    }
    
    static function setData(array $data) {
        self::$data = $data;
    }
    
    function getConnection() {
        return $this->connection;
    }
    
}
