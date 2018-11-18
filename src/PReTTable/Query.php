<?php

namespace PReTTable;

use ArrayObject;

class Query {
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $containsSet;
    
    private $isContainedSet;
    
    private $joins;

    function __construct($modelName) {
        $this->modelName = $modelName;
        $this->model = Reflection::getDeclarationOf($modelName);
        $this->tableName = Model::resolveTableName($modelName);
        
        $this->containsSet = [];
        $this->isContainedSet = [];
        
        $this->joins = new ArrayObject();
    }
    
    function contains($modelName, $relatedColumn = '', $through = '') {
        Model::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $this->containsSet[$modelName] = [];
        
        if (empty($through)) {
            $this->containsSet[$modelName]['relatedColumn'] = $relatedColumn;
        } else {
            Model::checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->containsSet[$modelName]['associativeModel'] = $through;
            
            $this->contains($through);
        }
        
    }
    
    function isContained($modelName, $relatedColumn = '', $through = '') {
        Model::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->isContainedSet[$modelName] = [];
        
        if (empty($through)) {
            $this->isContainedSet[$modelName]['relatedColumn'] = $relatedColumn;
        } else {
            Model::checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->isContainedSet[$modelName]['associativeModel'] = $through;
            
            $this->isContained($through, $relatedColumn);
        }
    }
    
    function getContainsSet() {
        return $this->containsSet;
    }
    
    function getIsContainedSet() {
        return $this->isContainedSet;
    }
    
    function setContainsSet($containsSet) {
        $this->containsSet = $containsSet;
    }
    
    function setIsContainedSet($isContainedSet) {
        $this->isContainedSet = $isContainedSet;
    }
    
    function getRow($column, $value = '') {
        $map = [
            'select' => '',
            'from' => '',
            'where' => ''
        ];
        
        if (count(func_get_args()) == 1) {
            $value = $column;
            $column = $this->model::getPrimaryKey();
        }
        
        $selectStatement = self::mountColumnsStatement($this->modelName);
        
        $map['select'] = implode(", ", $selectStatement->getArrayCopy());
        $map['from']   = $this->tableName;
        $map['where']  = "$this->tableName.{$column} = '$value'";
        
        return $map;
    }
    
    function getAll() {
        $map = [
            'select' => '',
            'from' => ''
        ];
        
        $selectStatement = self::mountColumnsStatement($this->modelName);
        
        $map['select'] = implode(", ", $selectStatement->getArrayCopy());
        $map['from']   = $this->tableName;
        
        return $map;
    }
    
//     mudar aqui para suportar receber uma lista
    function join($modelName, $relatedColumn) {
        Model::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        if (
            !in_array($modelName, $this->joins->getArrayCopy())
            && (array_key_exists($modelName, $this->containsSet)
                || array_key_exists($modelName, $this->isContainedSet))
            ) {
            
            $this->joins->offsetSet($modelName, $relatedColumn);
            
        }
        
        return $this;
    }
    
    function select($modelName) {
        $columns = [];
        
        $map = [
            'select' => '',
            'from' => '',
            'joins' => []
        ];
        
        $relatedModel = Reflection::getDeclarationOf($modelName);
        $relatedTableName = Model::resolveTableName($modelName);
        
        if (array_key_exists($modelName, $this->containsSet) ||
            array_key_exists($modelName, $this->isContainedSet)) {
                
            $selectStatement = Model::mountColumnsStatement($modelName, true);
            $map['select'] = implode(", ", $selectStatement->getArrayCopy());
            $map['from']   = $relatedTableName;
            
            if (array_key_exists($modelName, $this->containsSet)) {
                if (array_key_exists('associativeModel', $this->containsSet[$modelName])) {
                    $associativeModelName = $this->containsSet[$modelName]['associativeModel'];
                    $associativeTableName = Model::resolveTableName($associativeModelName);
                    $map['from'] = $associativeTableName;
                    
                    $associativeModel = Reflection::getDeclarationOf($associativeModelName);
                    
                    $fk = $associativeModel::getForeignKeyOf($this->modelName);
                    
                    array_push($map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
                    $fk = $associativeModel::getForeignKeyOf($modelName);
                    
                    array_push($map['joins'],
                        "$relatedTableName ON $relatedTableName.{$relatedModel::getPrimaryKey()} = $associativeTableName.{$fk}");
                } else {
                    if (is_subclass_of($modelName, __NAMESPACE__ . '\AbstractAssociativeModel')) {
                        $fk = $relatedModel::getForeignKeyOf($this->modelName);
                        array_push($map['joins'],
                            "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.$fk");
                    } else {
                        array_push($map['joins'],
                            "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                    }
                }
            } else {
                if (array_key_exists('associativeModel', $this->isContainedSet[$modelName])) {
                    $associativeModelName = $this->isContainedSet[$modelName]['associativeModel'];
                    $associativeTableName = Model::resolveTableName($associativeModelName);
                    $map['from'] = $associativeTableName;
                    
                    $associativeModel = Reflection::getDeclarationOf($associativeModelName);
                    
                    $fk = $this->isContainedSet[$associativeModelName]['relatedColumn'];
                    
                    array_push($map['joins'],
                        "$this->tableName ON $this->tableName.$fk = $associativeTableName.{$associativeModel::getPrimaryKey()}");
                    
                    $fk = $associativeModel::getForeignKeyOf($modelName);
                    
                    array_push($map['joins'],
                        "$relatedTableName ON $relatedTableName.{$relatedModel::getPrimaryKey()} = $associativeTableName.$fk");
                } else {
                    if (is_subclass_of($modelName, __NAMESPACE__ . '\AbstractAssociativeModel')) {
                        $fk = $this->isContainedSet[$modelName]['relatedColumn'];
                        array_push($map['joins'],
                            "$this->tableName ON $this->tableName.$fk = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                    } else {
                        $fk = $this->isContainedSet[$modelName]['relatedColumn'];
                        array_push($map['joins'],
                            "$this->tableName ON $this->tableName.$fk = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                    }
                }
            }
            
        }
        
        if ($this->joins->count()) {
            foreach ($this->joins as $modelName => $relatedColumn) {
                $model = Reflection::getDeclarationOf($modelName);
                $tableName = Model::resolveTableName($modelName);
                
                if (array_key_exists($modelName, $this->containsSet)) {
                    $joinedModelColumn = $this->containsSet[$modelName]['relatedColumn'];
                } else {
                    $joinedModelColumn = $this->isContainedSet[$modelName]['relatedColumn'];
                }
                
                array_push($map['joins'],
                    "$tableName ON $tableName.$joinedModelColumn = $this->tableName.$relatedColumn");
            }
        }
        
        return $map;
    }
    
}
