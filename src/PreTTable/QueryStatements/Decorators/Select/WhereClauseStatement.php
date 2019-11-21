<?php

namespace PreTTable\QueryStatements\Decorators\Select;

use
    PreTTable\Helpers\SQL,
    PreTTable\WhereClause
;

class WhereClauseStatement {
    
    private $statement;
    
    private $involvedTableNames;
    
    private $options = [];
    
    function __construct(WhereClause\InvolvedTableNames $involvedTableNames = null) {
        $this->statement = '';
        
        $this->involvedTableNames = $involvedTableNames;
        
        $this->options = [
            'comparisonOperator' => '=',
            'logicalOperator' => 'AND'
        ];
    }
    
    function like($columnName, $value, $options = []) {
        $clone = $this->getClone();
        
        $value = SQL\ValueAdjuster::adjust([$value])[0];
        
        $columnStatement = $columnName;
        
        if (isset($clone->involvedTableNames)) {
            $tableName = $clone->involvedTableNames->getTableNameOfColumnName($columnName);
            
            if (isset($tableName)) {
                $columnStatement = "$tableName.$columnName";
            }
        }
        
        $statement = "($columnStatement LIKE $value)";
        $clone->addsStatement($statement, $options);
        
        return $clone;
    }
    
    function between($columnName, $start, $end, $options = []) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($clone->involvedTableNames)) {
            $tableName = $clone->involvedTableNames->getTableNameOfColumnName($columnName);
            
            if (isset($tableName)) {
                $columnStatement = "$tableName.$columnName";
            }
        }
        
        $statement = "$columnStatement BETWEEN $start AND $end";
        $clone->addsStatement($statement, $options);
        
        return $clone;
    }
    
    function getStatement() {
        return $this->statement;
    }
    
    function addsStatements(array $params, $options = []) {
        $clone = $this->getClone();
        
        foreach($params as $columnName => $value) {
            $clone->addsStatement2($columnName, $value, $options);
        }
        
        return $clone;
    }
    
    private function addsStatement($statement, $options = []) {
        if (array_key_exists('logicalOperator', $options)) {
            $logicalOperator = $options['logicalOperator'];
        } else {
            $logicalOperator = $this->options['logicalOperator'];
        }
        
        if (empty($this->statement)) {
            $this->statement .= $statement;
        } else {
            $this->statement .= "\n\n\t\t$logicalOperator $statement";
        }
    }
    
//     if there are equal columns, it is correct to add a statement manually for each one that repeats. These columns should not be mapped.
    private function addsStatement2($columnName, $value, $options = []) {
        if (array_key_exists('comparisonOperator', $options)) {
            $comparisonOperator = $options['comparisonOperator'];
        } else {
            $comparisonOperator = $this->options['comparisonOperator'];
        }
        
        $columnStatement = $columnName;
        
        if (isset($this->involvedTableNames)) {
            $tableName = $this->involvedTableNames->getTableNameOfColumnName($columnName);
            if (isset($tableName)) {
                $columnStatement = "$tableName.$columnName";
            }
        }
        
        if (gettype($value) == 'array') {
            if (count($value)) {
                $value = SQL\ValueAdjuster::adjust($value);
                $valuesStatement = implode(", ", $value);
                $statement = "($columnStatement IN ($valuesStatement))";
            }
        } else {
            $value = SQL\ValueAdjuster::adjust([$value])[0];
            $statement = "($columnStatement $comparisonOperator $value)";
        }
        
        if (isset($statement)) {
            $this->addsStatement($statement, $options);
        }
        
        return $this;
    }
    
    private function getClone() {
        return clone $this;
    }
    
}
