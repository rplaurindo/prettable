<?php

namespace PReTTable\QueryStatements;

use 
    PReTTable\Repository\QueryMap,
    PReTTable\Reflection;

class Select {
    
    function getStatement($attachTable, ...$modelNames) {
        $count = count($modelNames);
        
        if ((gettype($attachTable) == 'boolean' && $attachTable)
            || (gettype($attachTable) == 'string' && $count >= 1)) {
            
            if (gettype($attachTable) == 'string') {
                array_push($modelNames, $attachTable);
            }
            
            return $this->mountCollection(...$modelNames);
        }
        
        return implode(', ', $this->mountMember($attachTable, false));        
    }
    
    private function mountCollection(...$modelNames) {
        $mountedColumns = [];
        
        foreach($modelNames as $modelName) {
            $mountedColumns = array_merge($mountedColumns, $this->mountMember($modelName, true));
        }
        
        return implode(', ', $mountedColumns);
    }
    
    private function mountMember($modelName, $attachTableName) {
//         echo "$modelName\n\n";
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
