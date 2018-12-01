<?php

namespace PReTTable;

require 'autoload.php';

abstract class AbstractModel {
    
    private $host;
    
    private $connection;
    
    private $queryMap;

    function __construct($host, array $data) {
        $this->host = $host;
        
        Connection::setData($data);
        
        $this->queryMap = new QueryMap($this::class);
    }
    
    function establishConnection($database, $host = null) {
        if (isset($host)) {
            $this->host = $host;
        }
        
        $connection = new Connection();
        $this->connection = $connection->establishConnection($this->host, $database);
    }
    
    function create(array $attributes) {
//         use try e catch na porra toda
    }
    
//     put proxy methods (from QueryMap) here to relate models
    
    function createAssociation($primaryKeyValue, $associationModelName, 
                               $attributes, $associationAttributes) {
        
    }
    
    private function getClone() {
        return clone $this;
    }
    
}


