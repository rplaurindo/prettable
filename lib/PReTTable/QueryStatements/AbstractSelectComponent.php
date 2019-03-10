<?php

namespace PReTTable\QueryStatements;

abstract class AbstractSelectComponent {
    
    protected $_statement;
    
    protected $_connection;
    
    function __construct($statement) {
        $this->_statement = $statement;
    }
    
    abstract function getStatement();
    
    function setConnection($connection) {
        $this->_connection = $connection;
    }
    
    function getConnection() {
        $clone = $this->getClone();
        
        return $clone->_connection;
    }
    
    private function getClone() {
        return clone $this;
    }
    
}
