<?php

namespace PReTTable\Helpers;

class PDOWhereClause extends AbstractWhereClause {
    
    function __construct(...$tables) {
        parent::__construct(...$tables);
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
                    array_push($mounted, " $this->logicalOperator $columnName $this->comparisonOperator :$columnName");
                } else {
                    array_push($mounted, "$columnName $this->comparisonOperator :$columnName");
                }
            }
        }
        
        return implode("", $mounted);
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
                        array_push($mounted, " $this->logicalOperator $tableName.$columnName $this->comparisonOperator :$columnName");
                    } else {
                        array_push($mounted, "$tableName.$columnName $this->comparisonOperator :$columnName");
                    }
                }
            }
        }
        
        return implode("", $mounted);
    }
    
}
