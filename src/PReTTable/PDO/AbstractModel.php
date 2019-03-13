<?php

namespace PReTTable\PDO;

use
    PReTTable,
    PReTTable\Connections
;

// abstrair para um trait
abstract class AbstractModel extends PReTTable\AbstractModelBase {

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);

        Connections\PDOConnection::setData($connectionData);

        $this->connectionContext = new Connections\StrategyContext(new Connections\PDOConnection($this->environment));
    }

}
