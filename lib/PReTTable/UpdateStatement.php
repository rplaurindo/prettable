<?php

namespace PReTTable;

class UpdateStatement {
    
    private $updateStatement;
    
    private $setStatement;
    
    private $whereStatement;
    
    function __construct($modelName, $primaryKeyValue, array $attributes) {
        Model::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $tableName = Model::resolveTableName($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        
        $this->updateStatement = $tableName;
        
        $this->mountSet($attributes);
        
        $primaryKey = $model::getPrimaryKey();
        $this->whereStatement = "$primaryKey = $primaryKeyValue";
    }
    
    function getUpdateStatement() {
        return $this->updateStatement;
    }
    
    function getSetStatement() {
        return $this->setStatement;
    }
    
    function getWhereStatement() {
        return $this->whereStatement;
    }
    
    private function mountSet(array $attributes) {
        $mounted = [];
        
        foreach($attributes as $columnName => $value) {
            array_push($mounted, "$columnName = $value");
        }
        
        $this->setStatement = implode(", ", $mounted);
    }
    
}
