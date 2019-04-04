<?php

namespace PReTTable\PDO;

use
    PDO,
    PDOException,
    PReTTable\Connections
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
        return new Connections\PDOConnection($this->connectionData, $this->environment);
    }
    
    function execute() {
        echo "$this->queryStatement\n\n";
        
        try {
            $PDOstatement = $this->connection->query($this->queryStatement);
            
            foreach ($this->getValues2Bind() as $index => $value) {
                $PDOstatement->bindParam($index, $value);
            }
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }

}
