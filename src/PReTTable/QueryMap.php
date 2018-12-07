<?php

namespace PReTTable;

use Exception, ArrayObject;

// a layer to mount a map of queries to read data
class QueryMap {
    
    protected $modelName;
    
    protected $model;
    
    protected $tableName;
    
    protected $primaryKeyName;
    
    protected $associatedModelName;
    
    protected $associatedModel;
    
    protected $associatedTableName;
    
    protected $associativeModelName;
    
    protected $associativeTableName;
    
    protected $associativeModel;
    
    protected $containsSet;
    
    protected $isContainedSet;
    
    protected $select;
    
    protected $from;
    
    protected $joins;
    
    function __construct($modelName) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $this->modelName = $modelName;
        $this->model = Reflection::getDeclarationOf($modelName);
        $this->tableName = self::resolveTableName($modelName);
        $this->primaryKeyName = $this->model::getPrimaryKeyName();
        
        $this->containsSet = new ArrayObject();
        $this->isContainedSet = new ArrayObject();
        
        $this->joins = new ArrayObject();
//         $this->joins = [];
    }
    
    static function resolveTableName($modelName) {
        $model = Reflection::getDeclarationOf($modelName);
        
        $tableName = $model::getTableName();
        if (empty ($tableName)) {
            return $modelName;
        }
        
        return $tableName;
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
            throw new Exception("The model must be a $classesAsText");
        }
        
    }
    
    function contains($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface', __NAMESPACE__ . '\AssociativeModelInterface');
        
        $this->containsSet->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    function isContained($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $this->isContainedSet->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    function containsThrough($modelName, $through) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface', __NAMESPACE__ . '\AssociativeModelInterface');
        
        $this->containsSet->offsetSet($modelName, ['associativeModelName' => $through]);
    }
    
    function getAssociativeModelNameOf($modelName) {
        if ($this->containsSet->offsetExists($modelName)) {
            $relationshipData = $this->containsSet->offsetGet($modelName);
            
            if (array_key_exists('associativeModelName', $relationshipData)) {
                return $relationshipData['associativeModelName'];
            }
        }
        
        return null;
    }
    
    function select($modelName) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface', __NAMESPACE__ . '\AssociativeModelInterface');
        
        $clone = $this->getClone();
        
        $clone->associatedModelName = $modelName;
        $clone->associatedModel = Reflection::getDeclarationOf($modelName);
        $clone->associatedTableName = self::resolveTableName($modelName);
        
        if ($clone->containsSet->offsetExists($modelName) 
            || $clone->isContainedSet->offsetExists($modelName)) {
                
            $selectStatement = new SelectStatement($modelName);
            $clone->select = $selectStatement->mount(true);
            
            $clone->from = $clone->associatedTableName;
            
            if ($clone->containsSet->offsetExists($modelName)) {
                if (array_key_exists('associativeModelName', 
                        $clone->containsSet->offsetGet($modelName))
                    ) {
                    $clone->associativeModelName = $clone->getAssociativeModelNameOf($modelName);
                    $clone->associativeModel = Reflection::getDeclarationOf($clone->associativeModelName);
                    
                    $clone->associativeTableName = self::resolveTableName($clone->associativeModelName);
                    $clone->from = $clone->associativeTableName;
                    
                    $clone->join($clone->modelName, $clone->primaryKeyName);
                    $clone->join($modelName, $clone->associatedModel::getPrimaryKeyName());
                    
                    $associativeColumn = $clone->associativeModel::getAssociativeKeys()[$clone->modelName];
                } else {
                    $clone->join($clone->modelName, $clone->primaryKeyName);
                    
                    $associatedColumn = $clone->containsSet->offsetGet($modelName)['associatedColumn'];
                }
            } else {
                $associatedColumn = $clone->isContainedSet->offsetGet($modelName)['associatedColumn'];
                
                $clone->join($clone->modelName, $associatedColumn);
            }
        }
        
        return $clone;
    }
    
    function join($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface', __NAMESPACE__ . '\AssociativeModelInterface');
        
        $clone = $this->getClone();

        if (!$clone->joins->offsetExists($modelName)
            && ($clone->containsSet->offsetExists($modelName) 
                || $clone->isContainedSet->offsetExists($modelName))
            || $modelName == $clone->modelName
            ) {
            $clone->joins->offsetSet($modelName, $associatedColumn);
        }
        
        return clone $clone;
    }
    
    function getMap() {
        $map = [];
        
        if (isset($this->select)) {
            $map['select'] = $this->select;
        }
        
        if (isset($this->from)) {
            $map['from'] = $this->from;
        }
        
        if ($this->joins->count()) {
            $map['joins'] = [];
            
            foreach ($this->joins as $joinedModelName => $joinedColumnName) {
                $joinedTableName = self::resolveTableName($joinedModelName);
            
                if ($this->containsSet->offsetExists($joinedModelName)) {
                    if (array_key_exists('associativeModelName', $this->containsSet->offsetGet($joinedModelName))) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getAssociativeKeys()[$joinedModelName];
                    } else {
                        $tableName = $this->tableName;
                        $columnName = $this->primaryKeyName;
                    }
                } else {
                    if ($this->isContainedSet->offsetExists($joinedModelName)) {
                        $tableName = $this->tableName;
                        $columnName = $this->isContainedSet->offsetGet($joinedModelName)['associatedColumn'];
                    } else if (array_key_exists($this->associatedModelName, $this->isContainedSet)) {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->associatedModel::getPrimaryKeyName();
                    } else if (isset($this->associativeModelName)) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getAssociativeKeys()[$joinedModelName];
                    } else {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->containsSet->offsetGet($this->associatedModelName)['associatedColumn'];
                    }
                }
                
                array_push($map['joins'],
                    "$joinedTableName ON $joinedTableName.$joinedColumnName = $tableName.$columnName");
            }
        }
        
        return $map;
    }
    
    protected function getClone(){
        return clone $this;
    }
    
}
