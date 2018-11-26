<?php

namespace PReTTable\Helpers;

class WhereClause {
    
    private $tables;
    
    private $comparisonOperator;
    
    private $logicalOperator;
    
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
    
    private function mountWithAttachedTable(array $params) {
        $mounted = [];
        
        foreach ($this->tables as $tableName) {
            foreach($params[$tableName] as $columnName => $value) {
                if (gettype($value) == 'array') {
                    if (count($value)) {
                        $firstValue = $value[0];
                        $value = array_slice($value, 1);
                        
                        $statement = "$tableName.$columnName $this->comparisonOperator '$firstValue'";
                        foreach ($value as $v) {
                            $statement .= " OR $tableName.$columnName $this->comparisonOperator '$v'";
                        }
                        
                        if (count($mounted)) {
                            array_push($mounted, " $this->logicalOperator ($statement)");
                        } else {
                            array_push($mounted, "($statement)");
                        }
                    }
                } else {
                    if (count($mounted)) {
                        array_push($mounted, " $this->logicalOperator $tableName.$columnName $this->comparisonOperator '$value'");
                    } else {
                        array_push($mounted, "$tableName.$columnName $this->comparisonOperator '$value'");
                    }
                }
            }
        }
        
        return implode("", $mounted);
    }
    
    private function mountWithoutAttachedTable(array $params) {
        
        $mounted = [];
        
        foreach($params as $columnName => $value) {
            if (gettype($value) == 'array') {
                if (count($value)) {
                    $firstValue = $value[0];
                    $value = array_slice($value, 1);
                    
                    $statement = "$columnName $this->comparisonOperator '$firstValue'";
                    foreach ($value as $v) {
                        $statement .= " OR $columnName $this->comparisonOperator '$v'";
                    }
                    
                    if (count($mounted)) {
                        array_push($mounted, " $this->logicalOperator ($statement)");
                    } else {
                        array_push($mounted, "($statement)");
                    }
                }
            } else {
                if (count($mounted)) {
                    array_push($mounted, " $this->logicalOperator $columnName $this->comparisonOperator '$value'");
                } else {
                    array_push($mounted, "$columnName $this->comparisonOperator '$value'");
                }
            }
        }
        
        return implode("", $mounted);
    }
    
}
