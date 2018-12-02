<?php

namespace PReTTable;

class WritingStatement {
    
    function __construct() {
        
    }
    
    protected function resolveValues(array $values) {
        $resolvedValues = [];
        
        foreach ($values as $value) {
            if (gettype($value) == 'string') {
                $value = "'$value'";
            }
            array_push($resolvedValues, $value);
        }
        
        return $resolvedValues;
    }
    
}