<?php

namespace PReTTable;

class DeleteStatement {
    
    private $deleteFromStatement;
    
    private $whereStatement;
    
    function __construct($modelName, $columnName, ...$values) {
        Query::checkIfModelIs($modelName, __NAMESPACE__ . '\GeneralAbstractModel');
        
        $tableName = Query::resolveTableName($modelName);
        $this->deleteFromStatement = $tableName;
        
        $this->mountSet($attributes);
    }
    
    function getDeleteFromStatement() {
        return $this->deleteFromStatement;
    }
    
    function getWhereStatement() {
        return $this->whereStatement;
    }
    
}
