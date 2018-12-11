<?php

namespace PReTTable\Helpers;

abstract class AbstractWhereClauseStatement {
    
    protected $tableName;
    
    protected $comparisonOperator;
    
    protected $logicalOperator;
    
    protected $statement;
    
    function __construct($tableName = null) {
        $this->tableName = $tableName;
        $this->comparisonOperator = '=';
        $this->logicalOperator = 'AND';
        $this->statement = '';
    }
    
    function setComparisonOperator($operator) {
        $this->comparisonOperator = $operator;
    }
    
    function setLogicalOperator($operator) {
        $this->logicalOperator = $operator;
    }
    
    function addStatements(array $params) {
        $clone = $this->getClone();
        
        foreach($params as $columnName => $value) {
            $clone->addStatement($columnName, $value);
        }
        
        return $clone;
    }
    
    function addOr(AbstractWhereClauseStatement $statement) {
        $clone = $this->getClone();
        
        $clone->statement .= " OR ({$statement->getStatement()})";
        
        return $clone;
    }
    
    function addAnd(AbstractWhereClauseStatement $statement) {
        $clone = $this->getClone();
        
        $clone->statement .= " AND ({$statement->getStatement()})";
        
        return $clone;
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
    
    abstract function like($columnName, $value);
    
    abstract function between($columnName, $start, $end);
    
    protected abstract function addStatement($columnName, $value);
    
    protected function getClone() {
        return clone $this;
    }
}
