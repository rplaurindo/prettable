<?php

namespace PReTTable\QueryStatements;

use 
    PReTTable\QueryMap,
    PReTTable\Reflection;

class Select {
    
    private $models;
    
    function __construct(...$models) {
        $this->models = $models;
    }
    
    function getStatement($attachTableName = false) {
        
        if (count($this->models) > 1) {
            return $this->mountCollection($attachTableName);
        }
        
        return implode(', ', $this->mountMember($this->models[0], $attachTableName));
        
    }
    
    private function mountCollection($attachTableName) {
        $mountedColumns = [];
        
        foreach($this->models as $modelName) {
            $mountedColumns = array_merge($mountedColumns, $this->mountMember($modelName, $attachTableName));
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
