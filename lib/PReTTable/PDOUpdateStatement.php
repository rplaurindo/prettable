<?php

namespace PReTTable;

class PDOUpdateStatement {
    
    private $updateStatement;
    
    private $attributes;
    
    private $primaryKeyName;
    
    private $whereStatement;
    
    function __construct($modelName, array $attributes) {
        QueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $this->attributes = $attributes;
        
        $tableName = QueryMap::resolveTableName($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        
        $this->updateStatement = $tableName;
        
        $this->primaryKeyName = $model::getPrimaryKeyName();
        $this->whereStatement = "$this->primaryKeyName = :$this->primaryKeyName";
    }
    
    function getStatement() {
        $settings = [];
        foreach ($this->attributes as $columnName => $value) {
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
