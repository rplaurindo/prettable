<?php

namespace PReTTable;

class WritingStatement {
    
    function __construct() { }
    
    protected function resolveStringValues(...$rows) {
//         if (count($rows) === 1) {
//             $attributes = $rows[0];
            
//             foreach ($attributes as $columnName => $value) {
//                 if (gettype($value) == 'string') {
//                     $attributes[$columnName] = "'$value'";
//                 }
//             }
            
//             return $attributes;
//         } else {
            foreach ($rows as $index => $attributes) {
                foreach ($attributes as $columnName => $value) {
                    if (gettype($value) == 'string') {
                        $attributes[$columnName] = "'$value'";
                    }
                }
                
                $rows[$index] = $attributes; 
            }
            
            return $rows;
//         }
    }
    
}
