<?php

namespace PReTTable\QueryStatements;

use 
    PReTTable\Repository\RelationshipBuilding,
    PReTTable\Reflection;

class Select {
    
    function getStatement($attachTableName, ...$modelNames) {
        $count = count($modelNames);
        
        if ((gettype($attachTableName) == 'boolean' && $attachTableName)
            || (gettype($attachTableName) == 'string' && $count >= 1)) {
            
            if (gettype($attachTableName) == 'string') {
                array_push($modelNames, $attachTableName);
            }
            
            return $this->mountCollection(...$modelNames);
        }
        
        return implode(', ', $this->mountMember($attachTableName, false));        
    }
    
    private function mountCollection(...$modelNames) {
        $mountedColumns = [];
        
        foreach($modelNames as $modelName) {
            $mountedColumns = array_merge($mountedColumns, $this->mountMember($modelName, true));
        }
        
        return implode(', ', $mountedColumns);
    }
    
    private function mountMember($modelName, $attachTableName) {
        RelationshipBuilding::checkIfModelIs($modelName, 'PReTTable\ModelInterface');
        
        $model = Reflection::getDeclarationOf($modelName);
        $columns = $model::getColumns();
        
        if ($attachTableName) {
            $tableName = RelationshipBuilding::resolveTableName($modelName);
        }
        
        $mountedColumns = [];
            
        foreach($columns as $k => $v) {
            if (is_string($k)) {
                $columnName = $k;
                $alias = $v;
                if ($attachTableName) {
                    array_push($mountedColumns, "$tableName.$columnName as $tableName.$alias");
                } else {
                    array_push($mountedColumns, "$columnName as $alias");
                }
            } else {
                $columnName = $v;
                array_push($mountedColumns, ($attachTableName ? "$tableName.$columnName" : $columnName));
            }
        }
        
        return $mountedColumns;
    }
    
}
