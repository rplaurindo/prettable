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
        
        $this->containsSet = new ArrayObject();
        $this->isContainedSet = new ArrayObject();
        
        $this->joins = new ArrayObject();
//         $this->joins = [];
    }
    
    function contains($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $this->containsSet->offsetSet($modelName, ['relatedColumn' => $relatedColumn]);
    }
    
    function containsThrough($modelName, $through) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $this->containsSet->offsetSet($modelName, ['associativeModel' => $through]);
    }
    
    function isContained($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->isContainedSet->offsetSet($modelName, ['relatedColumn' => $relatedColumn]);
    }
    
    function read($modelName, $id = null) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $clone = $this->getClone();
        
        $clone->relatedModelName = $modelName;
        $clone->relatedModel = Reflection::getDeclarationOf($modelName);
        $clone->relatedTableName = self::resolveTableName($modelName);
        
        if ($clone->containsSet->offsetExists($modelName) 
            || $clone->isContainedSet->offsetExists($modelName)) {
                
            $selectStatement = new SelectStatement($modelName);
            $clone->select = $selectStatement->mount(true);
            
            $clone->from = $clone->relatedTableName;
            
            if ($clone->containsSet->offsetExists($modelName)) {
                if (array_key_exists('associativeModel', 
                    $clone->containsSet->offsetGet($modelName))
                    ) {
                    $clone->associativeModelName = $clone->containsSet->offsetGet($modelName)['associativeModel'];
                    $clone->associativeModel = Reflection::getDeclarationOf($clone->associativeModelName);
                    
                    $clone->associativeTableName = self::resolveTableName($clone->associativeModelName);
                    $clone->from = $clone->associativeTableName;
                    
                    $clone->join($clone->modelName, $clone->model::getPrimaryKey());
                    $clone->join($modelName, $clone->relatedModel::getPrimaryKey());
                } else {
                    $clone->join($clone->modelName, $clone->model::getPrimaryKey());
                }
            } else {
                $clone->join($clone->modelName, $clone->isContainedSet->offsetGet($modelName)['relatedColumn']);
            }
            
        }
        
        return $clone;
    }
    
    function join($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $clone = $this->getClone();

        if (!$clone->joins->offsetExists($modelName)
            && ($clone->containsSet->offsetExists($modelName) 
                || $clone->isContainedSet->offsetExists($modelName))
            || $modelName == $clone->modelName
            ) {
            $clone->joins->offsetSet($modelName, $relatedColumn);
        }
        
        return clone $clone;
    }
    
    function getRow($columnName, $value = '') {
        $clone = $this->getClone();
        
        if (count(func_get_args()) == 1) {
            $value = $column;
            $column = $clone->model::getPrimaryKey();
        }
        
        $selectStatement = new SelectStatement($clone->modelName);
        $clone->select = $selectStatement->mount();
        
        $clone->from   = $clone->tableName;
        $clone->where  = "$clone->tableName.$columnName = '$value'";
        
        return $clone;
    }
    
    function getAll() {
        $clone = $this->getClone();
        
        $selectStatement = new SelectStatement($clone->modelName);
        $clone->select = $selectStatement->mount();
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
        
        if ($this->joins->count()) {
            $map['joins'] = [];
            
            foreach ($this->joins as $joinedModelName => $joinedColumnName) {
                $joinedTableName = self::resolveTableName($joinedModelName);
            
                if ($this->containsSet->offsetExists($joinedModelName)) {
                    if (array_key_exists('associativeModel', $this->containsSet->offsetGet($joinedModelName))) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getForeignKeyOf($joinedModelName);
                    } else {
                        $tableName = $this->tableName;
                        $columnName = $this->model::getPrimaryKey();
                    }
                } else {
                    if ($this->isContainedSet->offsetExists($joinedModelName)) {
                        $tableName = $this->tableName;
                        $columnName = $this->isContainedSet->offsetGet($joinedModelName)['relatedColumn'];
                    } else if (array_key_exists($this->relatedModelName, $this->isContainedSet)) {
                        $tableName = $this->relatedTableName;
                        $columnName = $this->relatedModel::getPrimaryKey();
                    } else if (isset($this->associativeModelName)) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getForeignKeyOf($joinedModelName);
                    } else {
                        $tableName = $this->relatedTableName;
                        $columnName = $this->containsSet->offsetGet($this->relatedModelName)['relatedColumn'];
                    }
                }
                
                array_push($map['joins'],
                    "$joinedTableName ON $joinedTableName.$joinedColumnName = $tableName.$columnName");
            }
        }
        
        if (isset($this->where)) {
            $map['where'] = $this->where;
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
