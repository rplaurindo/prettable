<?php

namespace PReTTable\Helpers;

class SQL {
    
    static function mountColumnsStatement($fields, $tableName = '') {
        $mountedFields = [];
        
        if (empty($tableName)) {
            foreach($fields as $k => $v) {
                if (is_string($k)) {
                    array_push($mountedFields, "$k as $v");
                } else {
                    array_push($mountedFields, "$v");
                }
            }
        } else {
            foreach($fields as $k => $v) {
                if (is_string($k)) {
                    array_push($mountedFields, "$tableName.$k as $v");
                } else {
                    array_push($mountedFields, "$tableName.$v");
                }
            }
        }
        
        return implode(", ", $mountedFields);
    }
    
}
