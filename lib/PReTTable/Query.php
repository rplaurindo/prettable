<?php

namespace PReTTable;

class Query {

    private $selectStatement;
    
    private $fromStatement;
    
    function setSelectStatement($statement) {
        $this->selectStatement = $statement;
    }
    
    function setFromStatement($statement) {
        $this->fromStatement = $statement;
    }
    
    function getSelectStatement() {
        return $this->selectStatement;
    }
    
    function getFromStatement() {
        return $this->fromStatement;
    }

}
