<?php

namespace PReTTable\QueryStatements\PDO;

use 
    PDO
;

class SelectComponent extends AbstractSelectComponent {

    function __construct($statement) {
        parent::__construct($statement);
    }
    
    function setConnection(PDO $connection) {
        $this->_connection = $connection;
    }
    
    function getStatement() {
        return $this->_statement;
    }

}
