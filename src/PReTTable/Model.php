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
    
    private $select;
    
    private $map;
    
    function __construct($modelName) {
        if (!is_subclass_of($modelName, __NAMESPACE__ . '\AbstractModel')) {
            throw new Exception('The model must be an ' . __NAMESPACE__ . '\AbstractModel');
        }
        
        $this->modelName = $modelName;
        $this->model = self::getClassDeclaration($modelName);
        $this->tableName = self::resolveTableName($modelName);
        
        $this->contains = [];
        $this->isContained = [];
        
        $this->select = new ArrayObject();
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
        
        self::attachesAt($this->select, self::mountFieldsStatement($this->modelName));
        
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
        
        self::attachesAt($this->select, self::mountFieldsStatement($this->modelName));
        
        $this->map['select'] = implode(", ", $this->select->getArrayCopy());
        $this->map['from']   = $this->tableName;
        
        return $this->map;
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
            
            self::attachesAt($this->select, self::mountFieldsStatement($modelName, true));
            $this->map['select'] = implode(", ", $this->select->getArrayCopy());
            $this->map['from']   = $relatedTableName;
            
            if (array_key_exists($modelName, $this->contains)) {
                if (array_key_exists('associativeTable', $this->contains[$modelName])) {
                    $this->map['from'] = $this->contains[$modelName]['associativeTable'];
                    $associativeTableName = $this->map['from'];
                    
                    $associativeModel = self::getClassDeclaration($associativeTableName);
                    
                    $fk = $associativeModel::getForeignKeyOf($this->modelName);
                    
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
                    $fk = $associativeModel::getForeignKeyOf($modelName);
                    
                    array_push($this->map['joins'],
                        "$relatedTableNameName ON $relatedTableName.{$relatedModel::getPrimaryKey()} = $associativeTableName.{$fk}");
                } else {
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.{$relatedModel::getPrimaryKey()}");
                }
            } else {
                if (array_key_exists('associativeModel', $this->isContained[$modelName])) {
                    $this->map['from'] = $this->isContained[$modelName]['associativeTable'];
                    $associativeTableName = $this->map['from'];
                    
                    $associativeModel = self::getClassDeclaration($associativeTableName);
                    
                    $fk = $associativeModel::getForeignKeyOf($this->modelName);
                    
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
                    $fk = $associativeModel::getForeignKeyOf($modelName);
                    
                    array_push($this->map['joins'],
                        "$tableName ON $tableName.{$relatedModel::getPrimaryKey()} = $associativeTableName.{$fk}");
                } else {
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $tableName.{$relatedModel::getPrimaryKey()}");
                }
            }
            
        }
        
        return $this->map;
        
    }
    
    function contains($modelName, $foreignKey, $through = '') {
        
        if (!is_subclass_of($modelName, __NAMESPACE__ . '\AbstractModel')) {
            throw new Exception('The model must be an AbstractModel');
        }

        $this->contains[$modelName] = [
            'foreignKey' => $foreignKey
        ];
        
        $relatedTableSpecifications = $this->contains[$modelName];
        
        if (!empty($through)) {
            if (!is_subclass_of($through, __NAMESPACE__ . '\AbstractAssociativeModel')) {
                throw new Exception('The associative model must be an AbstractAssociativeModel');
            }
            $this->contains[$modelName]['associativeModel'] = $through;
        }
        
    }
    
    function isContained($tableName, $foreignKey, $through = '') {
        
        if (!is_subclass_of($tableName, __NAMESPACE__ . '\AbstractModel')) {
            throw new Exception('The model must be an AbstractModel');
        }
        
        $this->isContained[$tableName] = [
            'foreignKey' => $foreignKey
        ];
        
        $relatedTableSpecifications = $this->isContained[$tableName];
        
        if (!empty($through)) {
            $this->isContained[$tableName]['associativeTable'] = $through;
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
    
    private static function attachesAt(ArrayObject $list, $statement) {
        if (!in_array($statement, $list->getArrayCopy())) {
            $list->append($statement);
        }
    }
    
    private static function getClassDeclaration($modelName) {
        $reflection = new ReflectionClass($modelName);
        return $reflection->newInstanceWithoutConstructor();
    }
    
}
