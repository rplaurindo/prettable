<?php

namespace PReTTable;

use
//     AbstractConnection,
    Exception,
    PReTTable\Helpers\StringEncoding
;


abstract class AbstractModelBase {

    protected $connection;

    protected $stringEncoder;

    function __construct(array $connectionData) {
        $this->stringEncoder = new StringEncoding();
    }

//     abstract function getConnection(): AbstractConnection;

    abstract function getConnection();

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

}
