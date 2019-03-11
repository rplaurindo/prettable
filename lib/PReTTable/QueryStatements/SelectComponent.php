<?php

namespace PReTTable\QueryStatements;

class SelectComponent extends AbstractQueryComponent {

    function __construct($statement) {
        parent::__construct($statement);
    }
    
    function getStatement() {
        return $this->_statement;
    }

}
