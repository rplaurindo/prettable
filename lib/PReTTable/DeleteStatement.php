<?php

namespace PReTTable;

class DeleteStatement {
    
    private $deleteFromStatement;
    
    private $whereClauseStatement;
    
    function __construct($modelName, $columnName, ...$values) {
        ReadQueryMap::checkIfModelIs($modelName, __NAMESPACE__ . '\ModelInterface');
        
        $tableName = ReadQueryMap::resolveTableName($modelName);
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
