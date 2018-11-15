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
        $this->checkIfModelsAre(__NAMESPACE__ . '\AbstractModel', $modelName);
        
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
        $this->checkIfModelsAre(__NAMESPACE__ . 'AbstractModel', ...$models);
        
//         adicionar somente se ainda não foi adicionado, e se não consta em contains e nem em isContained.
        
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
//             checar se joins tem conteúdo, caso sim, pegar seus campos
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
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.{$relatedModel::getPrimaryKey()}");
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
    
    function contains($modelName, $foreignKey, $through = '') {
        
        $this->checkIfModelsAre(__NAMESPACE__ . '\AbstractModel', $modelName);

        $this->contains[$modelName] = [
            'foreignKey' => $foreignKey
        ];
        
        $relatedTableSpecifications = $this->contains[$modelName];
        
        if (!empty($through)) {
            $this->checkIfModelsAre(__NAMESPACE__ . '\AbstractAssociativeModel', $through);
            
            $this->contains[$modelName]['associativeModel'] = $through;
        }
        
    }
    
    function isContained($modelName, $foreignKey, $through = '') {
        
        $this->checkIfModelsAre(__NAMESPACE__ . '\AbstractModel', $modelName);
        
        $this->isContained[$modelName] = [
            'foreignKey' => $foreignKey
        ];
        
        $relatedTableSpecifications = $this->isContained[$modelName];
        
        if (!empty($through)) {
            $this->checkIfModelsAre(__NAMESPACE__ . '\AbstractAssociativeModel', $models);
            
            $this->isContained[$modelName]['associativeModel'] = $through;
        }
    }
    
    private function checkIfModelsAre($class, ...$models) {
        
        global $globalClass;
        $globalClass = $class;
        
        array_walk($models, function($modelName) {
            global $globalClass;
            $class = $globalClass;
            if (!is_subclass_of($modelName, $class)) {
                throw new Exception("The model must be a $class");
            }
        });
        
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
