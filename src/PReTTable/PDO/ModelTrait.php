<?php

namespace PReTTable\PDO;

use
    PReTTable\Connections
;

trait ModelTrait {

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);

        Connections\PDOConnection::setData($connectionData);

        $this->connectionContext = new Connections\StrategyContext(new Connections\PDOConnection($this->environment));
    }

}
