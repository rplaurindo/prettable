<?php

namespace PReTTable\Helpers;

class WhereClause extends AbstractWhereClause {
    
    function __construct(...$tables) {
        parent::__construct(...$tables);
    }
    
    function addStatement($columnName, $value, $tableName = null) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($tableName)) {
            $columnStatement = "$tableName.$columnName";
        }
        
        if (gettype($value) == 'array') {
            if (count($value)) {
                $value = self::resolveStringValues(...$value);
                $valuesStatement = implode(', ', $value);
                $statement = "($columnStatement IN ($valuesStatement))";
            }
        } else {
            $value = self::resolveStringValues($value)[0];
            $statement = "($columnStatement $clone->comparisonOperator $value)";
        }
        
        if (empty($clone->statement)) {
            $clone->statement .= $statement;
        } else {
            $clone->statement .= " $clone->logicalOperator $statement";
        }
        
        return $clone;
    }
    
    function addBetweenStatement($columnName, $start, $end, $tableName = null) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($tableName)) {
            $columnStatement = "$tableName.$columnName";
        }
        
        $start = self::resolveStringValues($start)[0];
        $end = self::resolveStringValues($end)[0];
        
        $betweenStatement = "$columnStatement BETWEEN $start AND $end";
        
        if (empty($clone->statement)) {
            $clone->statement .= "$betweenStatement";
        } else {
            $clone->statement .= " $clone->logicalOperator $betweenStatement";
        }
        
        return $clone;
    }
    
    protected function mountWithoutAttachedTable(array $params) {
        $clone = $this;
        
        foreach($params as $columnName => $value) {
            $clone = $clone->addStatement($columnName, $value);
        }
        
        return $clone;
    }
    
    protected function mountWithAttachedTable(array $params) {
        $clone = $this;
        
        foreach ($this->tables as $tableName) {
            foreach($params[$tableName] as $columnName => $value) {
                $clone = $clone->addStatement($columnName, $value, $tableName);
            }
        }
        
        return $clone;
    }
    
}
