<?php

namespace PReTTable;

use
    Exception,
    PReTTable\Helpers\StringEncoding
;

abstract class AbstractModelBase {

    protected $connectionContext;

    protected $connection;

    protected $environment;

    protected $stringEncoder;

    function __construct($environment = null, array $connectionData) {
        if (gettype($environment) == 'array') {
            $connectionData = $environment;
            $environment = null;
        }

        $this->environment = $environment;

        $this->stringEncoder = new StringEncoding();
    }

    protected function establishConnection($schemaName, $host = null) {
        if (!isset($schemaName)) {
            throw new Exception('A database schema should be passed.');
        }

        $this->connection = $this->connectionContext
            ->establishConnection($schemaName, $host);
    }

    //     to comply the Prototype pattern
    protected function getClone() {
        return clone $this;
    }

}
