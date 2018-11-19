<?php

namespace PReTTable\Helpers;

use ArrayObject;

class SelectStatement {
    
    private $tables;
    
    function __construct(...$tables) {
        
        $this->tables = new ArrayObject($tables);
        
    }
    
    function setTables(...$tables) {
        $this->tables = $tables;
    }
    
    function cleanTables() {
        $this->tables = [];
    }
    
    function mount(array $columns) {
        
        if ($this->tables->count()) {
            return $this->mountWithAttachedTable($columns);
        }
        
        return $this->mountWithoutAttachedTable($columns);
    }
    
    private function mountWithAttachTable($columns) {
        $mountedColumns = [];
        
        foreach($this->tables as $tableName) {
        
            foreach($columns[$tableName] as $columnName => $alias) {
                if (is_string($columnName)) {
                    array_push($mountedColumns, "$tableName.$columnName as $tableName.$alias");
                } else {
                    array_push($mountedColumns, "$tableName.$columnName");
                }
            }
            
        }
        
        return $mountedColumns;
    }
    
    private function mountWithoutAttachTable($columns) {
        $mountedColumns = [];
            
        foreach($columns[$tableName] as $columnName => $alias) {
            if (is_string($columnName)) {
                array_push($mountedColumns, "$columnName as $alias");
            } else {
                array_push($mountedColumns, "$columnName");
            }
        }
        
        return $mountedColumns;
    }
    
}
