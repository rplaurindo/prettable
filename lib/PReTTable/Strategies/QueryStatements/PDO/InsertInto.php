<?php

namespace PReTTable\Strategies\QueryStatements\PDO;

use 
    PReTTable\QueryMap,
    PReTTable\QueryStatementStrategyInterface;

class InsertInto implements QueryStatementStrategyInterface {
    
    private $tableName;
    
    function __construct($modelName) {
        QueryMap::checkIfModelIs($modelName, 'PReTTable\ModelInterface');
        
        $this->tableName = QueryMap::resolveTableName($modelName);
    }
    
    function getStatement(array $attributes) {
        $insertIntoStatement = "$this->tableName (" . implode(", ", array_keys($attributes)) . ")";
        
        $values = [];
        foreach ($attributes as $columnName => $value) {
            array_push($values, ":$columnName");
        }
        
        $valuesStatement = implode(', ', $values);
        
        $statement = "
            INSERT INTO $insertIntoStatement
            VALUES ($valuesStatement)
        ";
        
        return $statement;
        
    }
    
}
