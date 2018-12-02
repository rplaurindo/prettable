<?php

namespace PReTTable;

use Exception, PDOException;

abstract class AbstractModel {
    
    private $host;
    
    private $connection;
    
    private $queryMap;

    function __construct($host, array $data) {
        $this->host = $host;
        
        Connection::setData($data);
        
        try {
            $this->queryMap = new QueryMap(get_class($this));
        } catch (Exception $e) {
            echo $e;
        }
        
    }
    
    function establishConnection($database, $host = null) {
        if (isset($host)) {
            $this->host = $host;
        }
        
        $connection = new Connection();
        $this->connection = $connection->establishConnection($this->host, $database);
    }
    
    function create(array $attributes) {
        $map = $this->queryMap->insert($attributes)->getMap();
        
        print_r($map);
        
//         $insertInto = $map['insertInto'];
        
        $query = "
            
        ";
        
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


