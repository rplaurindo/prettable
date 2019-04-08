<?php

namespace PDO;

use
    PDO,
    PDOException,
    Connections\PDOConnection
;

trait ModelTrait {

    private $connectionData;

    private $environment;

    function __construct(array $connectionData, $environment = null) {
        parent::__construct($connectionData);

        $this->connectionData = $connectionData;
        $this->environment = $environment;
    }

    function getConnection() {
        return new PDOConnection($this->connectionData, $this->environment);
    }
    
    function execute($queryStatement, array $binds = []) {
        echo "$queryStatement\n\n";
        
        try {
            if (count($binds)) {
                $PDOstatement = $this->connection->prepare($queryStatement);
                
                foreach ($binds as $index => $value) {
                    if (gettype($index)) {
                        $PDOstatement->bindParam($index + 1, $value);
                    } else {
                        $PDOstatement->bindParam($index, $value);
                    }
                }
                
                $PDOstatement->execute();
            } else {
                $PDOstatement = $this->connection->query($queryStatement);
            }
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }

}
