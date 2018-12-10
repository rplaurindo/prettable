<?php

namespace PReTTable\Helpers\PDO;

use PReTTable\Helpers;

class WhereClause extends Helpers\AbstractWhereClause {
    
    function __construct(...$tables) {
        parent::__construct(...$tables);
    }
    
    protected function mountWithoutAttachedTable(array $params) {
        $mounted = [];
        
        foreach($params as $columnName => $value) {
            if (gettype($value) == 'array') {
                if (count($value)) {
                    $statement = implode(', ', $value);
                    if (count($mounted)) {
                        array_push($mounted, " $columnName IN ($statement)");
                    } else {
                        array_push($mounted, "$columnName IN ($statement)");
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
    
    protected function mountWithAttachedTable(array $params) {
        $mounted = [];
        
        foreach ($this->tables as $tableName) {
            foreach($params[$tableName] as $columnName => $value) {
                if (gettype($value) == 'array') {
                    if (count($value)) {
                        $statement = implode(', ', $value);
                        if (count($mounted)) {
                            array_push($mounted, " ($tableName.$columnName IN ($statement))");
                        } else {
                            array_push($mounted, "($tableName.$columnName IN ($statement))");
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
