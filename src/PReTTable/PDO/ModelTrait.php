<?php

namespace PReTTable\PDO;

use
    PReTTable\Connections
;

trait ModelTrait {

    function __construct($environment = null, array $connectionData) {
        parent::__construct(new Connections\PDOConnection($environment), $connectionData);
        Connections\PDOConnection::setData($connectionData);
    }

}
