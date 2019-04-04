<?php

namespace PReTTable\QueryStatements;

abstract class AbstractComponent {

    protected $_statement;

    function __construct($statement = '') {
        $this->_statement = $statement;
    }

    abstract function getStatement();

    private function getClone() {
        return clone $this;
    }

}
