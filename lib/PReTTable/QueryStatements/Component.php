<?php

namespace PReTTable\QueryStatements;

class Component extends AbstractComponent {

    function __construct($statement = '') {
        parent::__construct($statement);
    }
    
    function getStatement() {
        return $this->_statement;
    }

}
