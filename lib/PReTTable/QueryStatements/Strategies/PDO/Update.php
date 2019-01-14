<?php

namespace PReTTable\QueryStatements\Strategies\PDO;

use 
    PReTTable\Repository\RelationshipMap,
    PReTTable\Reflection,
    PReTTable\QueryStatementStrategyInterface;

class Update implements QueryStatementStrategyInterface {
    
    private $updateStatement;
    
    private $primaryKeyName;
    
    private $whereStatement;
    
    function __construct($modelName) {
        RelationshipMap::checkIfModelIs($modelName, 
            'PReTTable\Repository\IdentifiableModelInterface');
        
        $tableName = RelationshipMap::resolveTableName($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        
        $this->updateStatement = $tableName;
        
        $this->primaryKeyName = $model::getPrimaryKeyName();
        $this->whereStatement = 
            "$this->primaryKeyName = :$this->primaryKeyName";
    }
    
    function getStatement(array $attributes) {
        $settings = [];
        foreach ($attributes as $columnName => $value) {
            array_push($settings, "$columnName = :$columnName");
        }
        
        $settingsStatement = implode(', ', $settings);
        
        $statement = "
            UPDATE $this->updateStatement
            SET $settingsStatement
            WHERE $this->whereStatement
        ";
        
        return $statement;
    }
    
    function getPrimaryKeyName() {
        return $this->primaryKeyName;
    }
    
}
