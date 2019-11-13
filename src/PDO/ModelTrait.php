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
    
    protected function execute($sql, array $bindings = []) {
        echo "$sql\n\n";
        
        try {
            if (count($bindings)) {
                $statement = $this->connection->prepare($sql);
                
                foreach ($bindings as $index => $value) {
                    if (gettype($index)) {
                        $statement->bindParam($index + 1, $value);
                    } else {
                        $statement->bindParam($index, $value);
                    }
                }
                
                $statement->execute();
            } else {
                $statement = $this->connection->query($sql);
            }
            
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }

}
