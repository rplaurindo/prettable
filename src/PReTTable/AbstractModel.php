<?php

namespace PReTTable;

use PDOException;

abstract class AbstractModel {
    
    private $host;
    
    private $connection;
    
    private $queryMap;

    function __construct($host, array $data) {
        $this->host = $host;
        
        Connection::setData($data);
        
        $this->queryMap = new QueryMap($this::class);
    }
    
//     function reestablishConnection($database, $host = null) {
//         if (isset($host)) {
//             $this->host = $host;
//         }
        
//     }
    
//     static function establishConnection($host, array $data) {
    function establishConnection($database, $host = null) {
        if (isset($host)) {
            $this->host = $host;
        }
        
        $connection = new Connection();
        
//         return $connection->establishConnection($this->host, $database);
        $this->connection = $connection->establishConnection($this->host, $database);
    }
    
//     static function create(array $attributes) {
    function create(array $attributes) {
        try {
//             use $this->queryMap here
//             return lastInsertId
        } catch (PDOException $e) {
            echo $e;
        }
    }
    
//     put proxy methods (from QueryMap) here to relate models
    
    function createAssociation($primaryKeyValue, $associationModelName, 
                               $attributes, $associationAttributes) {
        
    }
    
    private function getClone() {
        return clone $this;
    }
    
}


