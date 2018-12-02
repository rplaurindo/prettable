<?php

namespace PReTTable;

class WritingStatement {
    
    function __construct() {
        
    }
    
    protected function resolveStringValues(array $attributes) {
        $resolved = [];
        
        foreach ($attributes as $column => $value) {
            if (gettype($value) == 'string') {
                $value = "'$value'";
            }
            
            $resolved[$column] = $value;
        }
        
        return $resolved;
    }
    
}
