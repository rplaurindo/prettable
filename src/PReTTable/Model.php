<?php

namespace PReTTable;

use Exception;

class Model {
    
    private $modelName;
    
    private $query;
    
    function __construct($modelName) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->modelName = $modelName;
        
        $this->query = new Query($modelName);
    }
    
    function contains($modelName, $relatedColumn = '', $through = '') {
        $this->query->contains($modelName, $relatedColumn, $through);
    }
    
    function isContained($modelName, $relatedColumn = '', $through = '') {
        $this->query->isContained($modelName, $relatedColumn, $through);
    }
    
    function getRow($column, $value = '') {
        return $this->query->getRow($column, $value);
    }
    
    function getAll() {
        return $this->getAll();
    }
    
    function join($modelName, $relatedColumn) {
        $query = new Query($modelName);
        $query->setContainsSet($this->query->getContainsSet());
        $query->setIsContainedSet($this->query->getIsContainedSet());
        
        return $query->join($modelName, $relatedColumn);
    }
    
    function select($modelName) {
        return $this->query->select($modelName);
    }
    
    static function resolveTableName($modelName) {
        $model = Reflection::getDeclarationOf($modelName);
        
        $tableName = $model::getTableName();
        if (empty ($tableName)) {
            return $modelName;
        }
        
        return $tableName;
    }
    
    static function mountColumnsStatement($modelName, $attachTable = false) {
        $columns = [];
        $model = Reflection::getDeclarationOf($modelName);
        if ($attachTable) {
            $tableName = self::resolveTableName($modelName);
            return Helpers\SQL::mountColumnsStatement($model::getColumns(), $tableName);
        }
        
        return Helpers\SQL::mountColumnsStatement($model::getColumns());
    }
    
    static function checkIfModelIs($modelName, ...$classes) {
        
        $count = 0;
        
        foreach ($classes as $class) {
            if (is_subclass_of($modelName, $class)) {
                $count++;
            }
        }
        
        if (!$count) {
            $classesAsText = implode(" or ", $classes);
            throw new Exception("The model must be a $classesAsText}");
        }
        
    }
    
}
