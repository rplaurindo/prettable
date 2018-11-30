<?php

namespace PReTTable;

class InsertIntoStatement {
    
    private $insertIntoStatement;
    
    private $valuesStatement;
    
    function __construct($modelName, array $attributes) {
        QueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\ModelInterface');
        
        $tableName = QueryMap::resolveTableName($modelName);
        
        $this->insertIntoStatement = "$tableName (" . implode(", ", array_keys($attributes)) . ")";
        $this->valuesStatement = "(" . implode(", ", array_values($attributes)) . ")";
        
    }
    
    function getInsertIntoStatement() {
        return $this->insertIntoStatement;
    }
    
    function getValuesStatement() {
        return $this->valuesStatement;
    }
    
}