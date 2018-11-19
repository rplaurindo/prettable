<?php

namespace PReTTable;

use Exception, ArrayObject;

class Model extends AbstractModelPrototype {
    
    private $modelName;
    
    private $tableName;
    
    private $model;
    
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
    
    function contains($modelName, $relatedColumn = '', $through = '') {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $this->containsSet[$modelName] = [];
        
        if (empty($through)) {
            $this->containsSet[$modelName]['relatedColumn'] = $relatedColumn;
        } else {
            self::checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->containsSet[$modelName]['associativeModel'] = $through;
            
            $this->contains($through, $relatedColumn);
        }
    }
    
    function isContained($modelName, $relatedColumn = '', $through = '') {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel');
        
        $this->isContainedSet[$modelName] = [];
        
        if (empty($through)) {
            $this->isContainedSet[$modelName]['relatedColumn'] = $relatedColumn;
        } else {
            self::checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->isContainedSet[$modelName]['associativeModel'] = $through;
            
            $this->isContained($through, $relatedColumn);
        }
    }
    
    function select($modelName) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $clone = $this->getClone();
        
        $relatedModel = Reflection::getDeclarationOf($modelName);
        $relatedTableName = self::resolveTableName($modelName);
        
        if (array_key_exists($modelName, $clone->containsSet) ||
            array_key_exists($modelName, $clone->isContainedSet)) {
                
            $clone->select = self::mountColumnsStatement($modelName, true);
            $clone->from = $relatedTableName;
            
            if (array_key_exists($modelName, $clone->containsSet)) {
                if (array_key_exists('associativeModel', $clone->containsSet[$modelName])) {
                    $clone->associativeModelName = $clone->containsSet[$modelName]['associativeModel'];
                    $clone->associativeModel = Reflection::getDeclarationOf($clone->associativeModelName);
                    
                    $clone->associativeTableName = self::resolveTableName($clone->associativeModelName);
                    $clone->from = $clone->associativeTableName;
                    
                    $clone->join($clone->modelName, $clone->model::getPrimaryKey());
                    
                    $clone->join($modelName, $relatedModel::getPrimaryKey());
                } else {
                    if (is_subclass_of($modelName, __NAMESPACE__ . '\AbstractAssociativeModel')) {
                        $clone->join($modelName, $clone->model::getPrimaryKey());
                    } else {
                        $clone->join($modelName, $clone->model::getPrimaryKey());
                    }
                }
            } else {
                if (array_key_exists('associativeModel', $clone->isContainedSet[$modelName])) {
                    $associativeModelName = $clone->isContainedSet[$modelName]['associativeModel'];
                    $this->associativeModel = Reflection::getDeclarationOf($associativeModelName);
                    $associativeTableName = self::resolveTableName($associativeModelName);
                    
                    $clone->from = $associativeTableName;
                    
//                     $fk = $this->isContainedSet[$associativeModelName]['relatedColumn'];
                    
//                     array_push($map['joins'],
//                         "$this->tableName ON $this->tableName.$fk = $associativeTableName.{$associativeself::getPrimaryKey()}");
                    
//                     $fk = $associativeself::getForeignKeyOf($modelName);
                    
//                     array_push($map['joins'],
//                         "$relatedTableName ON $relatedTableName.{$relatedself::getPrimaryKey()} = $associativeTableName.$fk");
                } else {
                    if (is_subclass_of($modelName, __NAMESPACE__ . '\AbstractAssociativeModel')) {
//                         $fk = $this->isContainedSet[$modelName]['relatedColumn'];
//                         array_push($map['joins'],
//                             "$this->tableName ON $this->tableName.$fk = $relatedTableName.{$relatedself::getPrimaryKey()}");
                    } else {
//                         $fk = $this->isContainedSet[$modelName]['relatedColumn'];
//                         array_push($map['joins'],
//                             "$this->tableName ON $this->tableName.$fk = $relatedTableName.{$relatedself::getPrimaryKey()}");
                    }
                }
            }
            
        }
        
        return $clone;
    }
    
    function join($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $clone = $this->getClone();
        
//         if (!in_array($modelName, $clone->joins->getArrayCopy())
//             && (array_key_exists($modelName, $clone->containsSet)
//                 || array_key_exists($modelName, $clone->isContainedSet))
//             ) {

//         echo "model name: $modelName\n\n";
//         echo "related column: $relatedColumn\n\n";

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
        
        if (!empty($this->select)) {
            $map['select'] = $this->select;
        }
        
        if (!empty($this->from)) {
            $map['from'] = $this->from;
        }
        
//         print_r($this->joins);
        
//         print_r($this->isContainedSet);
        
        if ($this->joins->count()) {
            $map['joins'] = [];
            foreach ($this->joins as $joinedModelName => $joinedColumnName) {
                $joinedTableName = self::resolveTableName($joinedModelName);
                
                if (array_key_exists($joinedModelName, $this->containsSet)) {
                    if (array_key_exists('associativeModel', $this->containsSet[$joinedModelName])) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getForeignKeyOf($joinedModelName);
                    } else {
                        $joinedTableName = $this->tableName;
                        $tableName = self::resolveTableName($joinedModelName);
                        $columnName = $this->containsSet[$joinedModelName]['relatedColumn'];
                    }
                } else {
                    $tableName = $this->associativeTableName;
                    $columnName = $this->associativeModel::getForeignKeyOf($joinedModelName);;
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
