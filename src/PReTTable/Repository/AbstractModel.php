<?php

namespace PReTTable\Repository;

use
    PReTTable\Connections\PDOConnection,
    PReTTable\Repository
;

abstract class AbstractModel extends Repository\AbstractModel {

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);

        PDOConnection::setData($this->connectionData);
    }

}
