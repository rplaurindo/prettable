<?php

namespace PReTTable;

use Exception, ArrayObject;

class Model extends AbstractModelPrototype {
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $containsSet;
    
    private $isContainedSet;
    
    private $select;
    
    private $from;
    
//     private $joinsMap;
    
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
//         $this->joinsMap = new ArrayObject();
    }
    
    function contains($modelName, $relatedColumn = '', $through = '') {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $this->containsSet[$modelName] = [];
        
        if (empty($through)) {
            $this->containsSet[$modelName]['relatedColumn'] = $relatedColumn;
        } else {
            self::checkIfModelIs($through, __NAMESPACE__ . '\AbstractAssociativeModel');
            $this->containsSet[$modelName]['associativeModel'] = $through;
            
            $this->contains($through);
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
        
        $cloned = $this->getClone();
        
        $relatedModel = Reflection::getDeclarationOf($modelName);
        $relatedTableName = self::resolveTableName($modelName);
        
        if (array_key_exists($modelName, $cloned->containsSet) ||
            array_key_exists($modelName, $cloned->isContainedSet)) {
                
            $cloned->select = self::mountColumnsStatement($modelName, true);
            $cloned->from = $relatedTableName;
            
            if (array_key_exists($modelName, $cloned->containsSet)) {
                if (array_key_exists('associativeModel', $cloned->containsSet[$modelName])) {
                    $associativeModelName = $cloned->containsSet[$modelName]['associativeModel'];
                    $associativeTableName = self::resolveTableName($associativeModelName);
                    
                    $cloned->from = $associativeTableName;
                    
                    $associativeModel = Reflection::getDeclarationOf($associativeModelName);
                    
//                     $fk = $associativeself::getForeignKeyOf($cloned->modelName);
                    
//                     $cloned->join($cloned->tableName, $cloned->model::getPrimaryKey());

//                     print_r($cloned);

//                     var_dump($cloned);
                    
                    echo "\n\n";
                    
//                     array_push($map['joins'],
//                         "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
//                     $fk = $associativeself::getForeignKeyOf($modelName);
                    
//                     array_push($map['joins'],
//                         "$relatedTableName ON $relatedTableName.{$relatedself::getPrimaryKey()} = $associativeTableName.{$fk}");

//                     $cloned->join($relatedTableName, $relatedModel::getPrimaryKey());
                } else {
                    if (is_subclass_of($modelName, __NAMESPACE__ . '\AbstractAssociativeModel')) {
//                         $fk = $relatedself::getForeignKeyOf($this->modelName);
//                         array_push($map['joins'],
//                             "$this->tableName ON $this->tableName.{$this->model::getPrimaryKey()} = $relatedTableName.$fk");
                    } else {
                        $cloned->join($modelName, $cloned->model::getPrimaryKey());
                    }
                }
            } else {
                if (array_key_exists('associativeModel', $cloned->isContainedSet[$modelName])) {
                    $associativeModelName = $cloned->isContainedSet[$modelName]['associativeModel'];
                    $associativeTableName = self::resolveTableName($associativeModelName);
                    
                    $cloned->from = $associativeTableName;
                    
                    $associativeModel = Reflection::getDeclarationOf($associativeModelName);
                    
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
        
        return $cloned;
    }
    
    function join($modelName, $relatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\AbstractModel', __NAMESPACE__ . '\AbstractAssociativeModel');
        
        $cloned = $this->getClone();
        
        if (!in_array($modelName, $cloned->joins->getArrayCopy())
            && (array_key_exists($modelName, $cloned->containsSet)
                || array_key_exists($modelName, $cloned->isContainedSet))
            ) {
            $cloned->joins->offsetSet($modelName, $relatedColumn);
        }
        
        return $cloned;
    }
    
    function getRow($columnName, $value = '') {
        $cloned = $this->getClone();
        
        if (count(func_get_args()) == 1) {
            $value = $column;
            $column = $cloned->model::getPrimaryKey();
        }
        
        $cloned->select = self::mountColumnsStatement($cloned->modelName);
        $cloned->from   = $cloned->tableName;
        $cloned->where  = "$cloned->tableName.$columnName = '$value'";
        
        return $cloned;
    }
    
    function getAll() {
        $cloned = $this->getClone();
        
        $cloned->select = self::mountColumnsStatement($cloned->modelName);
        $cloned->from   = $this->tableName;
        
        return $cloned;
    }
    
    function getMap() {
        $map = [];
        
        if (!empty($this->select)) {
            $map['select'] = $this->select;
        }
        
        if (!empty($this->from)) {
            $map['from'] = $this->from;
        }
        
        if ($this->joins->count()) {
            $map['joins'] = [];
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
            throw new Exception("The model must be a $classesAsText}");
        }
        
    }
    
}
