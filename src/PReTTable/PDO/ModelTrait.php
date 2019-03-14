<?php

namespace PReTTable\PDO;

use
    PReTTable\Connections
;

trait ModelTrait {

    private $connectionData;

    private $environment;

    function __construct(array $connectionData, $environment = null) {
        parent::__construct($connectionData);

        $this->connectionData = $connectionData;
        $this->environment = $environment;
    }

    function getConnection() {
        return new Connections\PDOConnection($this->connectionData, $this->environment);
    }

}
