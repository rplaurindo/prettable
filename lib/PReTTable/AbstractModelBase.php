<?php

namespace PReTTable;

use
    Exception,
    PReTTable\Helpers\StringEncoding
;

abstract class AbstractModelBase {

    protected $connectionContext;

    protected $connection;

    protected $stringEncoder;

    function __construct(AbstractConnection $connectionContext, array $connectionData) {
        $this->stringEncoder = new StringEncoding();

        $this->connectionContext = $connectionContext;
    }

    protected function establishConnection($schemaName, $host = null) {
        if (!isset($schemaName)) {
            throw new Exception('A database schema should be passed.');
        }

        $this->connection = $this->connectionContext
            ->establishConnection($schemaName, $host);
    }

    protected function getClone() {
        return clone $this;
    }

}
