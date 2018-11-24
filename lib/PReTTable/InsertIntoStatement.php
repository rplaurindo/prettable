<?php

namespace PReTTable;

class InsertIntoStatement {
    
    private $insertIntoStatement;
    
    private $values;
    
    function __construct($modelName, array $attributes) {
        Model::checkIfModelIs($modelName, __NAMESPACE__ . '\GeneralAbstractModel');
        
        $tableName = Model::resolveTableName($modelName);
        
        $this->insertIntoStatement = "$tableName (" . implode(", ", array_keys($attributes)) . ")";
        $this->values = "(" . implode(", ", array_values($attributes)) . ")";
        
    }
    
    function getInsertIntoStatement() {
        return $this->insertIntoStatement;
    }
    
    function getValues() {
        return $this->values;
    }
    
}