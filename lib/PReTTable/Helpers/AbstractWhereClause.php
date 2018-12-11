<?php

namespace PReTTable\Helpers;

abstract class AbstractWhereClause {
    
    protected $tables;
    
    protected $comparisonOperator;
    
    protected $logicalOperator;
    
    protected $statement;
    
    function __construct(...$tables) {
        $this->tables = $tables;
        $this->comparisonOperator = '=';
        $this->logicalOperator = 'AND';
        $this->statement = '';
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
    
    function getStatement() {
        return $this->statement;
    }
    
    protected static function resolveStringValues(...$values) {
        $resolved = [];
        
        foreach ($values as $value) {
            if (gettype($value) == 'string') {
                array_push($resolved, "'$value'");
            } else {
                array_push($resolved, $value);
            }
        }
        
        return $resolved;
    }
    
    protected abstract function mountWithAttachedTable(array $params);
    
    protected abstract function mountWithoutAttachedTable(array $params);
    
    abstract function addStatement($columnName, $value, $tableName = null);
    
    abstract function addBetweenStatement($columnName, $start, $end, $tableName = null);
    
    protected function getClone() {
        return clone $this;
    }
}
