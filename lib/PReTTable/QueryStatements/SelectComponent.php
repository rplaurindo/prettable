<?php

namespace PReTTable\QueryStatements;

class SelectComponent extends AbstractSelectComponent {

    function __construct($statement) {
        parent::__construct($statement);
    }
    
    function getStatement() {
        return $this->_statement;
    }

}
