<?php

namespace PReTTable;

use ArrayObject;
use Exception;
use ReflectionClass;

class Model {
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $contains;
    
    private $isContained;
    
    private $associations;
    
    private $select;
    
    private $joins;
    
    private $map;
    
    function __construct($modelName) {
        $this->checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->modelName = $modelName;
        $this->model = self::getClassDeclaration($modelName);
        $this->tableName = self::resolveTableName($modelName);
        
        $this->contains = [];
        $this->isContained = [];
        $this->associations = new ArrayObject();
        
        $this->select = new ArrayObject();
        $this->joins = new ArrayObject();
    }
    
    function getRow($column, $value = '') {
        $this->map = [
            'select' => '',
            'from' => '',
            'where' => ''
        ];
        
        if (count(func_get_args()) == 1) {
            $value = $column;
            $column = $this->model::getPrimaryKey();
        }
        
        self::cleanList($this->select);
        $this->attachesSelectStatement(self::mountColumnsStatement($this->modelName));
        
        $this->map['select'] = implode(", ", $this->select->getArrayCopy());
        $this->map['from']   = $this->tableName;
        $this->map['where']  = "$this->tableName.{$column} = '$value'";
        
        return $this->map;
    }
    
    function getAll() {
        $this->map = [
            'select' => '',
            'from' => ''
        ];
        
        self::cleanList($this->select);
        $this->attachesSelectStatement(self::mountColumnsStatement($this->modelName));
        
        $this->map['select'] = implode(", ", $this->select->getArrayCopy());
        $this->map['from']   = $this->tableName;
        
        return $this->map;
    }
    
    function join($modelName, $relatedColumn) {
        
        $this->checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        if (
            !in_array($modelName, $this->joins->getArrayCopy())
            && (array_key_exists($modelName, $this->contains)
                || array_key_exists($modelName, $this->isContained))
            ) {
                $this->joins->offsetSet($modelName, $relatedColumn);
        }
        
        return $this;
    }
    
    function select($modelName) {
        
        $columns = [];
        
        $this->map = [
            'select' => '',
            'from' => '',
            'joins' => []
        ];
        
        $relatedModel = self::getClassDeclaration($modelName);
        $relatedTableName = self::resolveTableName($modelName);
        
        if (array_key_exists($modelName, $this->contains) || 
            array_key_exists($modelName, $this->isContained)) {
             
            self::cleanList($this->select);
            $this->attachesSelectStatement(self::mountColumnsStatement($modelName, true));
            $this->map['select'] = implode(", ", $this->select->getArrayCopy());
            $this->map['from']   = $relatedTableName;
            
            if (array_key_exists($modelName, $this->contains)) {
                if (array_key_exists('associativeModel', $this->contains[$modelName])) {
                    $associativeModelName = $this->contains[$modelName]['associativeModel'];
                    $associativeTableName = self::resolveTableName($associativeModelName);
                    $this->map['from'] = $associativeTableName;
                    
                    $associativeModel = self::getClassDeclaration($associativeModelName);
                    
                    $fk = $associativeModel::getForeignKeyOf($this->modelName);
                    
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
                    $fk = $associativeModel::getForeignKeyOf($modelName);
                    
                    array_push($this->map['joins'],
                        "$relatedTableName ON $relatedTableName.{$relatedModel::getPrimaryKey()} = $associativeTableName.{$fk}");
                } else {
                    if (is_subclass_of($modelName, __NAMESPACE__ . '\AbstractAssociativeModel')) {
                        $fk = $relatedModel::getForeignKeyOf($this->modelName);
                        array_push($this->map['joins'],
                            "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.$fk");
                    } else {
                        array_push($this->map['joins'],
                            "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                    }
                }
            } else {
                if (array_key_exists('associativeModel', $this->isContained[$modelName])) {
                    $associativeModelName = $this->isContained[$modelName]['associativeModel'];
                    $associativeTableName = self::resolveTableName($associativeModelName);
                    $this->map['from'] = $associativeTableName;
                    
                    $associativeModel = self::getClassDeclaration($associativeModelName);
                    
                    $fk = $this->isContained[$associativeModelName]['relatedColumn'];
                    
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.$fk = $associativeTableName.{$associativeModel::getPrimaryKey()}");
                    
                    $fk = $associativeModel::getForeignKeyOf($modelName);
                    
                    array_push($this->map['joins'],
                        "$relatedTableName ON $relatedTableName.{$relatedModel::getPrimaryKey()} = $associativeTableName.$fk");
                } else {
                    if (is_subclass_of($modelName, __NAMESPACE__ . '\AbstractAssociativeModel')) {
                        $fk = $this->isContained[$modelName]['relatedColumn'];
                        array_push($this->map['joins'],
                            "$this->tableName ON $this->tableName.$fk = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                    } else {
                        $fk = $this->isContained[$modelName]['relatedColumn'];
                        array_push($this->map['joins'],
                            "$this->tableName ON $this->tableName.$fk = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                    }
                }
            }
            
        }
        
        if ($this->joins->count()) {
            foreach ($this->joins as $modelName => $relatedColumn) {
                $model = self::getClassDeclaration($modelName);
                $tableName = self::resolveTableName($modelName);

                if (array_key_exists($modelName, $this->contains)) {
                    $joinedModelColumn = $this->contains[$modelName]['relatedColumn'];
                } else {
                    $joinedModelColumn = $this->isContained[$modelName]['relatedColumn'];
                }

                array_push($this->map['joins'],
                    "$tableName ON $tableName.$joinedModelColumn = $this->tableName.$relatedColumn");
            }
        }
        
        return $this->map;
        
    }
    
    function contains($modelName, $relatedColumn = '', $through = '') {
        
        $this->checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');

        $this->contains[$modelName] = [];
        
        if (empty($through)) {
            $this->contains[$modelName]['relatedColumn'] = $relatedColumn;
        } else {
            $this->checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->contains[$modelName]['associativeModel'] = $through;
            
            $this->contains($through);
        }
        
    }
    
    function isContained($modelName, $relatedColumn='', $through = '') {
        
        $this->checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->isContained[$modelName] = [];
        
        if (empty($through)) {
            $this->isContained[$modelName]['relatedColumn'] = $relatedColumn;
        } else {
            $this->checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->isContained[$modelName]['associativeModel'] = $through;
            
            $this->isContained($through, $relatedColumn);
        }
    }
    
    private function checkIfModelIs($modelName, ...$classes) {
        
        global $globalModelName;
        global $globalCount;
        
        $globalModelName = $modelName;
        $count = 0;
        $globalCount = $count;
        
        array_walk($classes, function($class) {
            global $globalModelName;
            global $globalCount;
            
            $modelName = $globalModelName;
            
            if (is_subclass_of($modelName, $class)) {
                $globalCount++;
            }
            
        });
        
        if (!$globalCount) {
            $classesAsText = implode(" or ", $classes);
            throw new Exception("The model must be a $classesAsText}");
        }
        
    }
    
    private function attachesSelectStatement($statement) {
        if (!in_array($statement, $this->select->getArrayCopy())) {
            $this->select->append($statement);
        }
    }
    
    private static function resolveTableName($modelName) {
        $model = self::getClassDeclaration($modelName);
        
        $tableName = $model::getTableName();
        if (empty ($tableName)) {
            return $modelName;
        }
        
        return $tableName;
    }
    
    private static function mountColumnsStatement($modelName, $attachTable = false) {
        $columns = [];
        $model = self::getClassDeclaration($modelName);
        if ($attachTable) {
            $tableName = self::resolveTableName($modelName);
            return Helpers\SQL::mountColumnsStatement($model::getColumns(), $tableName);
        }
        
        return Helpers\SQL::mountColumnsStatement($model::getColumns());
    }
    
    private static function cleanList(ArrayObject $list) {
        $list->exchangeArray([]);
    }
    
    private static function getClassDeclaration($modelName) {
        $reflection = new ReflectionClass($modelName);
        return $reflection->newInstanceWithoutConstructor();
    }
    
}
