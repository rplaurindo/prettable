<?php

namespace PReTTable\QueryStatements;

use 
    PReTTable\Repository\QueryMap,
    PReTTable\Reflection;

class Select {
    
    function getStatement(...$modelNames) {
        
        if (count($modelNames) > 1) {
            return $this->mountCollection(...$modelNames);
        }
        
        return implode(', ', $this->mountMember($modelNames[0], false));        
    }
    
    private function mountCollection(...$modelNames) {
        $mountedColumns = [];
        
        foreach($modelNames as $modelName) {
            $mountedColumns = array_merge($mountedColumns, $this->mountMember($modelName, true));
        }
        
        return implode(', ', $mountedColumns);
    }
    
    private function mountMember($modelName, $attachTableName) {
        QueryMap::checkIfModelIs($modelName, 'PReTTable\ModelInterface');
        
        $model = Reflection::getDeclarationOf($modelName);
        $columns = $model::getColumns();
        
        if ($attachTableName) {
            $tableName = QueryMap::resolveTableName($modelName);
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
