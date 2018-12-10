<?php

namespace PReTTable\Helpers;

abstract class AbstractWhereClause {
    
    protected $tables;
    
    protected $comparisonOperator;
    
    protected $logicalOperator;
    
    function __construct(...$tables) {
        $this->tables = $tables;
        $this->comparisonOperator = '=';
        $this->logicalOperator = 'AND';
    }
    
    function attachTables(...$tables) {
        $this->tables = $tables;
    }
    
    function cleanTables() {
        $this->tables = [];
    }
    
    function setComparisonOperator($operator) {
        $this->comparisonOperator = $operator;
    }
    
    function setLogicalOperator($operator) {
        $this->logicalOperator = $operator;
    }
    
    function getStatement(array $params) {
        
        if (count($this->tables)) {
            return $this->mountWithAttachedTable($params);
        }
            
        return $this->mountWithoutAttachedTable($params);
        
    }
    
    protected abstract function mountWithAttachedTable(array $params);
    
    protected abstract function mountWithoutAttachedTable(array $params);
    
}
