<?php

namespace PReTTable\QueryStatements\Strategies\PDO;

use 
    PReTTable\Repository\RelationshipMap,
    PReTTable\QueryStatementStrategyInterface;

class InsertInto implements QueryStatementStrategyInterface {
    
    private $tableName;
    
    function __construct($modelName) {
        RelationshipMap::checkIfModelIs($modelName, 'PReTTable\ModelInterface');
        
        $this->tableName = RelationshipMap::resolveTableName($modelName);
    }
    
    function getStatement(array $attributes) {
        $insertIntoStatement = 
            "$this->tableName (" . implode(", ", array_keys($attributes)) . ")";
        
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
