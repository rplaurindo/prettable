<?php

namespace PDO;

use
    Connections\PDOConnection
    , PDO
    , PDOException
    , PDOStatement
;

trait ModelTraitProxy {
    
    protected $statement;
    
    protected $connection;
    
    private $bindings;
    
    function __construct(array $connectionData, $environment = null) {
        parent::__construct($connectionData, $environment);
        
        $this->bindings = [];
    }
    
    function setBindings(array $bindings) {
        $this->bindings = $bindings;
    }
    
    protected function establishConnection($schemaName) {
        $connection = new PDOConnection($this->connectionData);
        
        $this->connection = $connection->establishConnection($schemaName);
    }
    
    protected function execute($sql) {
        echo "$sql\n\n";
        
        try {
            if (count($this->bindings)) {
                $this->statement = $this->connection->prepare($sql);
                $this->linksParameters($this->statement);
                
                $this->statement->execute();
            } else {
                $this->statement = $this->connection->query($sql);
            }
            
            $this->statement->setFetchMode(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $this->statement;
    }
    
    private function linksParameters(PDOStatement $statement) {
        foreach ($this->bindings as $index => $value) {
            if (gettype($index)) {
                $statement->bindParam($index + 1, $value);
            } else {
                $statement->bindParam($index, $value);
            }
        }
    }

}
