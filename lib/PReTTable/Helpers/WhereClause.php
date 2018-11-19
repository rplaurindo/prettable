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
        $mountedColumns = [];
        
        foreach ($this->tables as $tableName) {
            foreach($params[$tableName] as $columnName => $value) {
                if (gettype($value) == 'array') {
                    
                    $firstValue = $value[0];
                    $value = array_slice($value, 1);
                    
                    $statement = "$tableName.$columnName $this->comparisonOperator '$firstValue'";
                    foreach ($value as $v) {
                        $statement .= " OR $tableName.$columnName $this->comparisonOperator '$v'";
                    }
                    
                    if (count($mountedColumns)) {
                        array_push($mountedColumns, " $this->logicalOperator ($statement)");
                    } else {
                        array_push($mountedColumns, "($statement)");
                    }
                } else {
                    if (count($mountedColumns)) {
                        array_push($mountedColumns, " $this->logicalOperator $tableName.$columnName $this->comparisonOperator '$value'");
                    } else {
                        array_push($mountedColumns, "$tableName.$columnName $this->comparisonOperator '$value'");
                    }
                }
            }
        }
        
        return implode("", $mountedColumns);
    }
    
    private function mountWithoutAttachedTable(array $params) {
        
        $mountedColumns = [];
        
        foreach($params as $columnName => $value) {
            if (gettype($value) == 'array') {
                
                $firstValue = $value[0];
                $value = array_slice($value, 1);
                
                $statement = "$columnName $this->comparisonOperator '$firstValue'";
                foreach ($value as $v) {
                    $statement .= " OR $columnName $this->comparisonOperator '$v'";
                }
                
                if (count($mountedColumns)) {
                    array_push($mountedColumns, " $this->logicalOperator ($statement)");
                } else {
                    array_push($mountedColumns, "($statement)");
                }
            } else {
                if (count($mountedColumns)) {
                    array_push($mountedColumns, " $this->logicalOperator $columnName $this->comparisonOperator '$value'");
                } else {
                    array_push($mountedColumns, "$columnName $this->comparisonOperator '$value'");
                }
            }
        }
        
        return implode("", $mountedColumns);
    }
    
}
