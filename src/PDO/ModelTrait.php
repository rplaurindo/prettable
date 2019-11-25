<?php

namespace PDO;

use
    Connections\PDOConnection
    , PDO
    , PDOException
    , PDOStatement
;

trait ModelTrait {
    
    private $bindings;
    
    function __construct(array $connectionData, $environment = null) {
        parent::__construct($connectionData, $environment);
        
        $this->bindings = [];
    }
    
    function setBindings(array $bindings) {
        $this->bindings = $bindings;
    }

    protected function getConnection() {
        return new PDOConnection($this->connectionData);
    }
    
    protected function execute($sql) {
        echo "$sql\n\n";
        
        try {
            if (count($this->bindings)) {
                $statement = $this->connection->prepare($sql);
                $this->linksParameters($statement);
                
                $statement->execute();
            } else {
                $statement = $this->connection->query($sql);
            }
            
            $statement->setFetchMode(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $statement;
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
