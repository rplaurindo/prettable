<?php

namespace PReTTable;

class PDOUpdateStatement extends WritingStatement {
    
    private $updateStatement;
    
    private $setStatement;
    
    private $whereStatement;
    
    function __construct($modelName, array $attributes) {
        ReadQueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $tableName = ReadQueryMap::resolveTableName($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        
        $this->updateStatement = $tableName;
        
//         $this->mountSet(...parent::resolveStringValues($attributes));
        $this->mountSet($attributes);
        
        $primaryKeyName = $model::getPrimaryKeyName();
        $this->whereStatement = "$primaryKeyName = :$primaryKeyName";
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
            array_push($mounted, "$columnName = :$columnName");
        }
        
        $this->setStatement = implode(", ", $mounted);
    }
    
}
