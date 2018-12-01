<?php

namespace PReTTable;

abstract class AbstractModel {
    
    private $host;
    
    private $connection;

    function __construct($host, array $data) {
        $this->host = $host;
        
        Connection::setData($data);
    }
    
    function establishConnection($database, $host = null) {
        if (isset($host)) {
            $this->host = $host;
        }
        
        $connection = new Connection();
        $this->connection = $connection->establishConnection($this->host, $database);
    }
    
//     put proxy methods (from QueryMap) here
    
    function createAssociation($primaryKeyValue, $associationModelName, 
                               $attributes, $associationAttributes) {
        
    }
    
}


