<?php

namespace PReTTable;

class PDOInsertIntoStatement extends WritingStatement {
    
    private $insertIntoStatement;
    
    private $rows;
    
    function __construct($modelName, ...$rows) {
        ReadQueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\ModelInterface');
        
        $tableName = ReadQueryMap::resolveTableName($modelName);
        $this->insertIntoStatement = "$tableName (" . implode(", ", array_keys($rows[0])) . ")";
        
        $this->rows = $rows;
    }
    
    function getStatements() {
        $statements = [];
        
        foreach ($this->rows as $attributes) {
            $values = [];
            foreach ($attributes as $columnName => $value) {
                array_push($values, ":$columnName");
            }
            
            $valuesStatement = implode(', ', $values);
            
            $statement = "
                INSERT INTO $this->insertIntoStatement
                VALUES ($valuesStatement)
            ";
            
            array_push($statements, $statement);
        }
        
        return $statements;
        
    }
    
}
