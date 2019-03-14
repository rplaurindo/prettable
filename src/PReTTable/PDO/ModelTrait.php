<?php

namespace PReTTable\PDO;

use
    PReTTable\Connections
;

trait ModelTrait {

    private $environment;

    function __construct($environment = null, array $connectionData) {
        parent::__construct($connectionData);
        Connections\PDOConnection::setData($connectionData);

        $this->environment = $environment;
    }

    function getConnection() {
        return new Connections\PDOConnection($this->environment);
    }

}
