<?php

namespace PReTTable;

class WritingStatement {
    
    function __construct() {
        
    }
    
    protected function resolveStringValues(...$rows) {
        foreach ($rows as $index => $attributes) {
            foreach ($attributes as $columnName => $value) {
                if (gettype($value) == 'string') {
                    $attributes[$columnName] = "'$value'";
                }
            }
            
            $rows[$index] = $attributes; 
        }

        return $rows;
    }
    
}
