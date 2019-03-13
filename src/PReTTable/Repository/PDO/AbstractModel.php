<?php

namespace PReTTable\Repository\PDO;

use
    PReTTable\Connections,
    PReTTable\Repository
;

abstract class AbstractModel extends Repository\AbstractModel {

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);

        $this->connectionContext = new Connections\StrategyContext(new Connections\PDOConnection($this->environment));
    }

}
