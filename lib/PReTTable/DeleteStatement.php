<?php

namespace PReTTable;

class DeleteStatement extends WritingStatement {
    
    private $deleteFromStatement;
    
    private $whereClauseStatement;
    
    function __construct($modelName, $columnName, ...$values) {
        QueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\ModelInterface');
        
        $tableName = QueryMap::resolveTableName($modelName);
        $this->deleteFromStatement = $tableName;
        
        $whereClause = new Helpers\WhereClause();
        $this->whereClauseStatement = $whereClause->mount([$columnName => $values]);
    }
    
    function getDeleteFromStatement() {
        return $this->deleteFromStatement;
    }
    
    function getWhereClauseStatement() {
        return $this->whereClauseStatement;
    }
    
}
