<?php

namespace PReTTable;

abstract class AbstractModel {
    
    private $connection;

//     quem herdar dessa classe vai ter de passar esses parâmetros, no entanto, a classe que herdar de quem herdar dessa classe não precisaria passar os dados
    function __construct($database, array $data) {
        Connection::setData($data);
        
        $connectionProxy = new Connection();
        
        $this->connection = $connectionProxy->getConnection();
    }
    
//     put proxy methods (from QueryMap) here

    
    
    function createAssociation($primaryKeyValue, $associationModelName, 
                               $attributes, $associationAttributes) {
        
    }
    
}


