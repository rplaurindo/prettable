<?php

namespace PReTTable\QueryStatements\Strategies\PDO;

use 
    PReTTable\Repository\RelationshipBuilding,
    PReTTable\QueryStatementStrategyInterface;

class InsertInto implements QueryStatementStrategyInterface {
    
    private $tableName;
    
    function __construct($modelName) {
        RelationshipBuilding::checkIfModelIs($modelName, 'PReTTable\ModelInterface');
        
        $this->tableName = RelationshipBuilding::resolveTableName($modelName);
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
