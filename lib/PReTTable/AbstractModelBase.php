<?php

namespace PReTTable;

use
//     AbstractConnection,
    ArrayObject,
    Exception,
    PReTTable\Helpers\StringEncoding
;


abstract class AbstractModelBase {

    protected $connection;

    protected $stringEncoder;
    
    protected $queryComponent;
    
    private $values2Bind;

    function __construct(array $connectionData) {
        $this->stringEncoder = new StringEncoding();
        
        $this->values2Bind = new ArrayObject();
    }

//     abstract function getConnection(): AbstractConnection;

    abstract function getConnection();
    
    function getValues2Bind() {
        return $this->values2Bind->getArrayCopy();
    }

    protected function establishConnection($schemaName) {
        if (!isset($schemaName)) {
            throw new Exception('A database schema should be passed.');
        }

//         $this->connection = $this->connectionContext
//             ->establishConnection($schemaName, $host);

        $this->connection = $this->getConnection()
            ->establishConnection($schemaName);
    }

//     to comply the Prototype pattern
    protected function getClone() {
        return clone $this;
    }
    
    protected function bind($index, $value) {
        $this->values2Bind->offsetSet($index, $value);
    }

}
