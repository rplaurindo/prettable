<?php

namespace PReTTable\Helpers;

class SelectStatement {
    
    private $tables;
    
    function __construct(...$tables) {
        
        $this->tables = $tables;
        
    }
    
    function attachTables(...$tables) {
        $this->tables = $tables;
    }
    
    function cleanTables() {
        $this->tables = [];
    }
    
    function mount(array $columns) {
        
        if (count($this->tables)) {
            return $this->mountWithAttachedTable($columns);
        }
        
        return $this->mountWithoutAttachedTable($columns);
    }
    
    private function mountWithAttachedTable($columns) {
        $mountedColumns = [];
        
        foreach($this->tables as $tableName) {
            foreach($columns[$tableName] as $columnName => $alias) {
                if (is_string($columnName)) {
                    array_push($mountedColumns, "$tableName.$columnName as $tableName.$alias");
                } else {
                    array_push($mountedColumns, "$tableName.$alias");
                }
            }
        }
        
        return implode(", ", $mountedColumns);
    }
    
    private function mountWithoutAttachedTable($columns) {
        $mountedColumns = [];
            
        foreach($columns[$tableName] as $columnName => $alias) {
            if (is_string($columnName)) {
                array_push($mountedColumns, "$columnName as $alias");
            } else {
                array_push($mountedColumns, "$alias");
            }
        }
        
        return implode(", ", $mountedColumns);
    }
    
}
