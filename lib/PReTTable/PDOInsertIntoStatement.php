<?php

namespace PReTTable;

class PDOInsertIntoStatement extends WritingStatement {
    
    private $connection;
    
    private $insertIntoStatement;
    
    private $rows;
    
    function __construct($modelName, $PDOConnection, ...$rows) {
        ReadQueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\ModelInterface');
        
        $this->connection = $PDOConnection;
        
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
            
            array_push($statements, $this->connection->prepare($statement));
        }
        
        return $statements;
        
    }
    
}
