<?php

namespace PReTTable\QueryStatements;

class SelectComponent extends AbstractSelectComponent {
    
    private $_statement;
    
    function setStatement($statement) {
        $this->_statement = $statement;
    }

    function getStatement() {
        return $this->_statement;
    }

}
