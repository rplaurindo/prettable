<?php

namespace PreTTable\QueryStatements\Decorators\Select;

use
    PreTTable\Helpers\SQL\ValueAdjuster
;

class WhereClauseStatement {

    private $tableName;
    
    private $statement;
    
    private $options = [];
    
    function __construct($tableName = null) {
        $this->tableName = $tableName;
        $this->statement = '';
        
        $this->options = [
            'comparisonOperator' => '=',
            'logicalOperator' => 'AND'
        ];
    }
    
    function like($columnName, $value, $options = []) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($clone->tableName)) {
            $columnStatement = "$clone->tableName.$columnName";
        }
        
        $value = ValueAdjuster::adjust([$value])[0];
        
        $statement = "($columnStatement LIKE $value)";
        $clone->addStatement($statement, $options);
        
        return $clone;
    }
    
    function between($columnName, $start, $end, $options = []) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($clone->tableName)) {
            $columnStatement = "$clone->tableName.$columnName";
        }
        
        $statement = "$columnStatement BETWEEN $start AND $end";
        $clone->addStatement($statement, $options);
        
        return $clone;
    }
    
    function getStatement() {
        return $this->statement;
    }
    
    function addStatements(array $params, $options = []) {
        $clone = $this->getClone();
        
        foreach($params as $columnName => $value) {
            $clone->addStatementTo($columnName, $value, $options);
        }
        
        return $clone;
    }
    
    private function addStatement($statement, $options = []) {
        if (array_key_exists('logicalOperator', $options)) {
            $logicalOperator = $options['logicalOperator'];
        } else {
            $logicalOperator = $this->options['logicalOperator'];
        }
        
        if (empty($this->statement)) {
            $this->statement .= $statement;
        } else {
            $this->statement .= "\n\t$logicalOperator $statement";
        }
    }
    
    private function addStatementTo($columnName, $value, $options = []) {
        if (array_key_exists('comparisonOperator', $options)) {
            $comparisonOperator = $options['comparisonOperator'];
        } else {
            $comparisonOperator = $this->options['comparisonOperator'];
        }
        
        $columnStatement = $columnName;
        
        if (isset($this->tableName)) {
            $columnStatement = "$this->tableName.$columnName";
        }
        
        if (gettype($value) == 'array') {
            if (count($value)) {
                $value = ValueAdjuster::adjust($value);
                $valuesStatement = implode(', ', $value);
                $statement = "($columnStatement IN ($valuesStatement))";
            }
        } else {
            $value = ValueAdjuster::adjust([$value])[0];
            $statement = "($columnStatement $comparisonOperator $value)";
        }
        
        if (isset($statement)) {
            $this->addStatement($statement, $options);
        }
        
        return $this;
    }
    
    private function getClone() {
        return clone $this;
    }

}
