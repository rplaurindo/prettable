<?php

namespace PReTTable;

class ConnectionContext {

    private $_connection;

    function __construct(ConnectionStrategyInterface $connection) {
        $this->_connection = $connection;
    }

    function establishConnection($schemaName, $host = null) {
        $clone = $this->getClone();

        return $clone->_connection->establishConnection($schemaName, $host);
    }

    private function getClone() {
        return clone $this;
    }

}
