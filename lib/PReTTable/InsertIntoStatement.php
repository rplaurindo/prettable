<?php

namespace PReTTable;

class InsertIntoStatement extends WritingStatement {
    
    private $insertIntoStatement;
    
    private $valuesStatement;
    
    function __construct($modelName, ...$rows) {
        QueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\ModelInterface');
        
        $tableName = QueryMap::resolveTableName($modelName);
        
        $this->insertIntoStatement = "$tableName (" . implode(", ", array_keys($rows[0])) . ")";
        $this->valuesStatement = implode(", ", $this->mountValues(...parent::resolveStringValues(...$rows)));
    }
    
    function getInsertIntoStatement() {
        return $this->insertIntoStatement;
    }
    
    function getValuesStatement() {
        return $this->valuesStatement;
    }
    
    private function mountValues(...$rows) {
        $values = [];
        
        foreach ($rows as $attributes) {
            array_push($values, "(" . implode(", ", array_values($attributes)) . ")");
        }
        
        return $values;
    }
    
}
