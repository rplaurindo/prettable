<?php

namespace PReTTable\PDO\Repository;

use
    PReTTable\Repository,
    PReTTable\Connections
;

abstract class AbstractModel extends Repository\AbstractModelBase {

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);

        Connections\PDOConnection::setData($connectionData);

        $this->connectionContext = new Connections\StrategyContext(new Connections\PDOConnection($this->environment));
    }

}
