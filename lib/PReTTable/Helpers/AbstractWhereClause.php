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
    
    function mount(array $params) {
        
        if (count($this->tables)) {
            return $this->mountWithAttachedTable($params);
        }
            
        return $this->mountWithoutAttachedTable($params);
        
    }
    
    private abstract function mountWithAttachedTable(array $params);
    
    private abstract function mountWithoutAttachedTable(array $params);
    
}
