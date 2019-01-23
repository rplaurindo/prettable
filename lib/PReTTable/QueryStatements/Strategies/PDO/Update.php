<?php

namespace PReTTable\QueryStatements\Strategies\PDO;

use 
    PReTTable\Repository\RelationshipBuilding,
    PReTTable\Reflection,
    PReTTable\QueryStatementStrategyInterface;

class Update implements QueryStatementStrategyInterface {
    
    private $updateStatement;
    
    private $whereStatement;
    
    function __construct($modelName) {
        RelationshipBuilding::checkIfModelIs($modelName, 
            'PReTTable\Repository\IdentifiableModelInterface');
        
        $tableName = RelationshipBuilding::resolveTableName($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        
        $this->updateStatement = $tableName;
        
        $primaryKeyName = $model::getPrimaryKeyName();
        $this->whereStatement = 
            "$primaryKeyName = :$primaryKeyName";
    }
    
    function getStatement(array $attributes) {
        $settings = [];
        foreach (array_keys($attributes) as $columnName) {
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
    
}
