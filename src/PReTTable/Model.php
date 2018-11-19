<?php

namespace PReTTable;

use Exception, ArrayObject;

class Model extends AbstractModelPrototype {
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $relatedModelName;
    
    private $relatedModel;
    
    private $relatedTableName;
    
    private $associativeModelName;
    
    private $associativeTableName;
    
    private $associativeModel;
    
    private $containsSet;
    
    private $isContainedSet;
    
    private $select;
    
    private $from;
    
    private $joins;
    
    private $where;
    
    function __construct($modelName) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->modelName = $modelName;
        $this->model = Reflection::getDeclarationOf($modelName);
        $this->tableName = self::resolveTableName($modelName);
        
        $this->containsSet = [];
        $this->isContainedSet = [];
        
        $this->joins = new ArrayObject();
    }
    
    function contains($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $this->containsSet[$modelName] = [];
        
        $this->containsSet[$modelName]['relatedColumn'] = $relatedColumn;
    }
    
    function containsThrough($modelName, $through) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        self::checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $this->containsSet[$modelName] = [];
        
        $this->containsSet[$modelName]['associativeModel'] = $through;
    }
    
    function isContained($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->isContainedSet[$modelName] = [];
        $this->isContainedSet[$modelName]['relatedColumn'] = $relatedColumn;
    }
    
    function select($modelName) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $clone = $this->getClone();
        
        $clone->relatedModelName = $modelName; 
        $clone->relatedModel = Reflection::getDeclarationOf($clone->relatedModelName);
        $clone->relatedTableName = self::resolveTableName($clone->relatedModelName);
        
        if (array_key_exists($modelName, $clone->containsSet) ||
            array_key_exists($modelName, $clone->isContainedSet)) {
                
            $clone->select = self::mountColumnsStatement($modelName, true);
            $clone->from = $clone->relatedTableName;
            
            if (array_key_exists($modelName, $clone->containsSet)) {
                if (array_key_exists('associativeModel', $clone->containsSet[$modelName])) {
                    $clone->associativeModelName = $clone->containsSet[$modelName]['associativeModel'];
                    $clone->associativeModel = Reflection::getDeclarationOf($clone->associativeModelName);
                    
                    $clone->associativeTableName = self::resolveTableName($clone->associativeModelName);
                    $clone->from = $clone->associativeTableName;
                    
                    $clone->join($clone->modelName, $clone->model::getPrimaryKey());
                    $clone->join($clone->relatedModelName, $clone->relatedModel::getPrimaryKey());
                    
//                     $clone->join($clone->relatedModelName, $clone->containsSet[$clone->modelName]['relatedColumn']);
                } else {
                    $clone->join($clone->modelName, $clone->model::getPrimaryKey());
                }
            } else {
                $clone->join($clone->modelName, $clone->isContainedSet[$modelName]['relatedColumn']);
            }
            
        }
        
        return $clone;
    }
    
    function join($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $clone = $this->getClone();

        if (!$clone->joins->offsetExists($modelName)) {
            $clone->joins->offsetSet($modelName, $relatedColumn);
        }
        
        return $clone;
    }
    
    function getRow($columnName, $value = '') {
        $clone = $this->getClone();
        
        if (count(func_get_args()) == 1) {
            $value = $column;
            $column = $clone->model::getPrimaryKey();
        }
        
        $clone->select = self::mountColumnsStatement($clone->modelName);
        $clone->from   = $clone->tableName;
        $clone->where  = "$clone->tableName.$columnName = '$value'";
        
        return $clone;
    }
    
    function getAll() {
        $clone = $this->getClone();
        
        $clone->select = self::mountColumnsStatement($clone->modelName);
        $clone->from   = $this->tableName;
        
        return $clone;
    }
    
    function getMap() {
        $map = [];
        
        if (isset($this->select)) {
            $map['select'] = $this->select;
        }
        
        if (isset($this->from)) {
            $map['from'] = $this->from;
        }
        
//         print_r($this->joins);
        
        if ($this->joins->count()) {
            $map['joins'] = [];
            foreach ($this->joins as $joinedModelName => $joinedColumnName) {
                $joinedTableName = self::resolveTableName($joinedModelName);
                
//                 print_r("$joinedModelName\n");
                
                if (array_key_exists($joinedModelName, $this->containsSet)) {
                    if (array_key_exists('associativeModel', $this->containsSet[$joinedModelName])) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getForeignKeyOf($joinedModelName);
                    } else {
//                         checked
                        $tableName = $this->tableName;
                        $columnName = $this->containsSet[$joinedModelName]['relatedColumn'];
                    }
                } else {
                    if (array_key_exists($this->relatedModelName, $this->isContainedSet)) {
                        $tableName = $this->relatedTableName;
                        $columnName = $this->relatedModel::getPrimaryKey();
                    } else if (isset($this->associativeModelName)) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getForeignKeyOf($joinedModelName);
                    } else {
//                         checked
                        $joinedTableName = self::resolveTableName($joinedModelName);
                        $tableName = $this->relatedTableName;
                        $columnName = $this->containsSet[$this->relatedModelName]['relatedColumn'];
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
    
    static function resolveTableName($modelName) {
        $model = Reflection::getDeclarationOf($modelName);
        
        $tableName = $model::getTableName();
        if (empty ($tableName)) {
            return $modelName;
        }
        
        return $tableName;
    }
    
    static function mountColumnsStatement($modelName, $attachTable = false) {
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
            throw new Exception("The model must be a $classesAsText");
        }
        
    }
    
}
