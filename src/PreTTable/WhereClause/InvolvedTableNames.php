<?php

namespace PreTTable\WhereClause;

use
    ArrayObject
;

class InvolvedTableNames {
    
    private $map;
    
    function __construct() {
        $this->map = new ArrayObject();
    }
    
    function getTableNameOfColumnName($columnName2Search) {
        foreach ($this->map->getArrayCopy() as $tableName => $columnNames) {
            foreach ($columnNames as $columnName) {
                if ($columnName === $columnName2Search) {
                    return $tableName;
                }
            }
        }
        
        return null;
    }
    
    function addsColumnsMap($tableName, array $columnNames) {
        $clone = $this->getClone();
        
        $clone->map->offsetSet($tableName, $columnNames);
        
        return $clone;
    }
    
    private function getClone() {
        return clone $this;
    }
    
}
