<?php

namespace PReTTable\Helpers;

class WhereClauseStatement extends AbstractWhereClauseStatement {
    
    function __construct($table = null) {
        parent::__construct($table);
    }
    
    function like($columnName, $value) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($clone->tableName)) {
            $columnStatement = "$clone->tableName.$columnName";
        }
        
        $value = self::resolveStringValues($value)[0];
        
        $statement = "($columnStatement LIKE $value)";
        $clone->addStatement($statement);
        
        return $clone;
    }
    
    function between($columnName, $start, $end) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($clone->tableName)) {
            $columnStatement = "$clone->tableName.$columnName";
        }
        
        $start = self::resolveStringValues($start)[0];
        $end = self::resolveStringValues($end)[0];
        
        $statement = "$columnStatement BETWEEN $start AND $end";
        $clone->addStatement($statement);
        
        return $clone;
    }
    
    protected function addStatementTo($columnName, $value) {
        $columnStatement = $columnName;
        
        if (isset($this->tableName)) {
            $columnStatement = "$this->tableName.$columnName";
        }
        
        if (gettype($value) == 'array') {
            if (count($value)) {
                $value = self::resolveStringValues(...$value);
                $valuesStatement = implode(', ', $value);
                $statement = "($columnStatement IN ($valuesStatement))";
            }
        } else {
            $value = self::resolveStringValues($value)[0];
            $statement = "($columnStatement $this->comparisonOperator $value)";
        }
        
        $this->addStatement($statement);
        
        return $this;
    }
    
}
