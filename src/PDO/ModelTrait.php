<?php

namespace PDO;

use
    PDO,
    PDOException,
    Connections\PDOConnection
;

trait ModelTrait {

    protected function getConnection() {
        return new PDOConnection($this->connectionData);
    }
    
    protected function execute($queryStatement, array $bindings = []) {
        echo "$queryStatement\n\n";
        
        try {
            if (count($bindings)) {
                $PDOstatement = $this->connection->prepare($queryStatement);
                
                foreach ($bindings as $index => $value) {
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
