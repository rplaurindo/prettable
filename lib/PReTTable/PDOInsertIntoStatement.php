<?php

namespace PReTTable;

class PDOInsertIntoStatement {
    
    private $tableName;
    
    function __construct($modelName) {
        QueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\ModelInterface');
        
        $this->tableName = QueryMap::resolveTableName($modelName);
    }
    
    function getStatements(...$rows) {
        $statements = [];
        
        $insertIntoStatement = "$this->tableName (" . implode(", ", array_keys($rows[0])) . ")";
        
        foreach ($rows as $attributes) {
            $values = [];
            foreach ($attributes as $columnName => $value) {
                array_push($values, ":$columnName");
            }
            
            $valuesStatement = implode(', ', $values);
            
            $statement = "
                INSERT INTO $insertIntoStatement
                VALUES ($valuesStatement)
            ";
            
            array_push($statements, $statement);
        }
        
        return $statements;
        
    }
    
}
