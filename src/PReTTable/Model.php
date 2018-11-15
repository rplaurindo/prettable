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
    
    function getRow($field, $value = '') {
        $this->map = [
            'select' => '',
            'from' => '',
            'where' => ''
        ];
        
        if (count(func_get_args()) == 1) {
            $value = $field;
            $field = $this->model::getPrimaryKey();
        }
        
        self::cleanList($this->select);
        self::attachesIn(self::mountFieldsStatement($this->modelName), $this->select);
        
        $this->map['select'] = implode(", ", $this->select->getArrayCopy());
        $this->map['from']   = $this->tableName;
        $this->map['where']  = "$this->tableName.{$field} = '$value'";
        
        return $this->map;
    }
    
    function getAll() {
        $this->map = [
            'select' => '',
            'from' => ''
        ];
        
        self::cleanList($this->select);
        self::attachesIn(self::mountFieldsStatement($this->modelName), $this->select);
        
        $this->map['select'] = implode(", ", $this->select->getArrayCopy());
        $this->map['from']   = $this->tableName;
        
        return $this->map;
    }
    
    function join(...$models) {
        
        array_walk($models, function($modelName) {
            
            $this->checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
            
            if (
                ($modelName == $this->modelName 
                    && (array_key_exists($modelName, $this->contains)
                        || array_key_exists($modelName, $this->isContained))
                )
                || ($modelName != $this->modelName
                    && !in_array($modelName, $this->joins->getArrayCopy()) 
                    && !array_key_exists($modelName, $this->contains)
                    && !array_key_exists($modelName, $this->isContained)
                )
            ) {
                
                self::attachesIn($modelName, $this->joins);
                
            }
        });
        
        return $this;
    }
    
    function select($modelName) {
        
        $fields = [];
        
        $this->map = [
            'select' => '',
            'from' => '',
            'joins' => []
        ];
        
        $relatedModel = self::getClassDeclaration($modelName);
        $relatedTableName = self::resolveTableName($modelName);
        
        if (array_key_exists($modelName, $this->contains) || 
            array_key_exists($modelName, $this->isContained)) {
//             checar se joins tem conte�do, caso sim, pegar seus campos se n�o houver uma associa��o em from
            self::cleanList($this->select);
            self::attachesIn(self::mountFieldsStatement($modelName, true), $this->select);
            $this->map['select'] = implode(", ", $this->select->getArrayCopy());
            $this->map['from']   = $relatedTableName;
            
            if (array_key_exists($modelName, $this->contains)) {
                if (array_key_exists('associativeModel', $this->contains[$modelName])) {
                    $associativeModel = $this->contains[$modelName]['associativeModel'];
                    $associativeTableName = self::resolveTableName($associativeModel);
                    $this->map['from'] = $associativeTableName;
                    
                    $associativeModel = self::getClassDeclaration($associativeModel);
                    
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
                    $associativeModel = $this->contains[$modelName]['associativeModel'];
                    $associativeTableName = self::resolveTableName($associativeModel);
                    $this->map['from'] = $associativeTableName;
                    
                    $associativeModel = self::getClassDeclaration($associativeModel);
                    
                    $fk = $associativeModel::getForeignKeyOf($this->modelName);
                    
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
                    $fk = $associativeModel::getForeignKeyOf($modelName);
                    
                    array_push($this->map['joins'],
                        "$relatedTableName ON $relatedTableName.{$relatedModel::getPrimaryKey()} = $associativeTableName.{$fk}");
                } else {
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                }
            }
            
        }
        
        return $this->map;
        
    }
    
    function contains($modelName, $foreignKey = '', $through = '') {
        
        $this->checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');

        $this->contains[$modelName] = [];
        
        if (empty($through)) {
            $this->contains[$modelName]['foreignKey'] = $foreignKey;
        } else {
            $this->checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->contains[$modelName]['associativeModel'] = $through;
            
            $this->contains($through);
        }
        
    }
    
    function isContained($modelName, $foreignKey, $through = '') {
        
        $this->checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->isContained[$modelName] = [];
        
        if (empty($through)) {
            $this->isContained[$modelName]['foreignKey'] = $foreignKey;
        } else {
            $this->checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->isContained[$modelName]['associativeModel'] = $through;
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
    
    private static function resolveTableName($modelName) {
        $model = self::getClassDeclaration($modelName);
        
        $tableName = $model::getTableName();
        if (empty ($tableName)) {
            return $modelName;
        }
        
        return $tableName;
    }
    
    private static function mountFieldsStatement($modelName, $attachTable = false) {
        $fields = [];
        $model = self::getClassDeclaration($modelName);
        if ($attachTable) {
            $tableName = self::resolveTableName($modelName);
            return Helpers\SQL::mountFieldsStatement($model::getFields(), $tableName);
        }
        
        return Helpers\SQL::mountFieldsStatement($model::getFields());
    }
    
    private static function cleanList(ArrayObject $list) {
        $list->exchangeArray([]);
    }
    
    private static function attachesIn($statement, ArrayObject $list) {
        if (!in_array($statement, $list->getArrayCopy())) {
            $list->append($statement);
        }
    }
    
    private static function getClassDeclaration($modelName) {
        $reflection = new ReflectionClass($modelName);
        return $reflection->newInstanceWithoutConstructor();
    }
    
}
