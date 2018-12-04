<?php

namespace PReTTable;

class UpdateStatement extends WritingStatement {
    
    private $updateStatement;
    
    private $setStatement;
    
    private $whereStatement;
    
    function __construct($modelName, $primaryKeyValue, array $attributes) {
        QueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $tableName = QueryMap::resolveTableName($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        
        $this->updateStatement = $tableName;
        
        $this->mountSet(...parent::resolveStringValues($attributes));
        
        $primaryKey = $model::getPrimaryKeyName();
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
