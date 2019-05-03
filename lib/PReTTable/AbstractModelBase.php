<?php

namespace PReTTable;

use
    Exception,
    PReTTable\Helpers\StringEncoding
;


abstract class AbstractModelBase {

    protected $connection;

    protected $stringEncoder;
    
    protected $connectionData;

    function __construct(array $connectionData, $environment = null) {
        $this->stringEncoder = new StringEncoding();
        
        $this->resolveConnectionDataEnvironment($connectionData, $environment);
    }

    protected abstract function getConnection();
    // protected abstract function getConnection(): AbstractConnection;

    protected function establishConnection($schemaName) {
        if (!isset($schemaName)) {
            throw new Exception('A database schema should be passed.');
        }

        $this->connection = $this->getConnection()
            ->establishConnection($schemaName);
    }

    // to comply the Prototype pattern
    protected function getClone() {
        return clone $this;
    }
    
    private function resolveConnectionDataEnvironment($connectionData, $environment = null) {
        if (isset($environment)) {
            $connectionData = $connectionData[$environment];
        }
        
        $this->connectionData = $connectionData;
    }

}
