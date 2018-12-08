<?php

namespace PReTTable\PDO;

use 
    PReTTable\QueryMap,
    PReTTable\Reflection;

class UpdateStatement {
    
    private $updateStatement;
    
    private $primaryKeyName;
    
    private $whereStatement;
    
    function __construct($modelName) {
        QueryMap::checkIfModelIs($modelName, 'PReTTable\IdentifiableModelInterface');
        
        $tableName = QueryMap::resolveTableName($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        
        $this->updateStatement = $tableName;
        
        $this->primaryKeyName = $model::getPrimaryKeyName();
        $this->whereStatement = "$this->primaryKeyName = :$this->primaryKeyName";
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
