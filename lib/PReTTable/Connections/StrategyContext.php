<?php

namespace PReTTable\Connections;

class StrategyContext {

    private $_connection;

    function __construct(StrategyInterface $connection) {
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
