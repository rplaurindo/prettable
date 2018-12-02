<?php

namespace PReTTable;

use Exception, ArrayObject;

// class QueryMap extends AbstractQueryPrototype {
class QueryMap {
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $primaryKey;
    
    private $associatedModelName;
    
    private $associatedModel;
    
    private $associatedTableName;
    
    private $associativeModelName;
    
    private $associativeTableName;
    
    private $associativeModel;
    
    private $containsSet;
    
    private $isContainedSet;
    
    private $select;
    
    private $from;
    
    private $joins;
    
    private $whereClause;
    
    private $insertInto;
    
    private $values;
    
    private $update;
    
    private $set;
    
    private $deleteFrom;
    
    function __construct($modelName) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $this->modelName = $modelName;
        $this->model = Reflection::getDeclarationOf($modelName);
        $this->tableName = self::resolveTableName($modelName);
        $this->primaryKey = $this->model::getPrimaryKey();
        
        $this->containsSet = new ArrayObject();
        $this->isContainedSet = new ArrayObject();
        
        $this->joins = new ArrayObject();
//         $this->joins = [];
    }
    
    function getModel() {
        return $this->model;
    }
    
    function contains($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface', __NAMESPACE__ . '\AssociativeModelInterface');
        
        $this->containsSet->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    function containsThrough($modelName, $through) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface', __NAMESPACE__ . '\AssociativeModelInterface');
        
        $this->containsSet->offsetSet($modelName, ['associativeModel' => $through]);
    }
    
    function isContained($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $this->isContainedSet->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    function select($modelName, $primaryKeyValue = null) {
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
                if (array_key_exists('associativeModel', 
                        $clone->containsSet->offsetGet($modelName))
                    ) {
                    $clone->associativeModelName = $clone->containsSet->offsetGet($modelName)['associativeModel'];
                    $clone->associativeModel = Reflection::getDeclarationOf($clone->associativeModelName);
                    
                    $clone->associativeTableName = self::resolveTableName($clone->associativeModelName);
                    $clone->from = $clone->associativeTableName;
                    
                    $clone->join($clone->modelName, $clone->primaryKey);
                    $clone->join($modelName, $clone->associatedModel::getPrimaryKey());
                    
                    $associativeColumn = $clone->associativeModel::getForeignKeyOf($clone->modelName);
                    if (isset($primaryKeyValue) && !empty($primaryKeyValue)) {
                        $clone->whereClause = "$clone->associativeTableName.$associativeColumn = $primaryKeyValue";
                    }
                } else {
                    $clone->join($clone->modelName, $clone->primaryKey);
                    
                    $associatedColumn = $clone->containsSet->offsetGet($modelName)['associatedColumn'];
                    if (isset($primaryKeyValue) && !empty($primaryKeyValue)) {
                        $clone->whereClause = "$clone->associatedTableName.$associatedColumn = $primaryKeyValue";
                    }
                }
            } else {
                $associatedColumn = $clone->isContainedSet->offsetGet($modelName)['associatedColumn'];
                
                $clone->join($clone->modelName, $associatedColumn);
                
                if (isset($primaryKeyValue) && !empty($primaryKeyValue)) {
                    $clone->whereClause = "$clone->tableName.$associatedColumn = $primaryKeyValue";
                }
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
    
    function getRow($columnName, $value = null) {
        $clone = $this->getClone();
        
        if (empty($value)) {
            $value = $columnName;
            $columnName = $clone->primaryKey;
        }
        
        $selectStatement = new SelectStatement($clone->modelName);
        $clone->select = $selectStatement->mount();
        
        $clone->from   = $clone->tableName;
        $clone->whereClause  = "$columnName = '$value'";
        
        return $clone;
    }
    
    function getAll() {
        $clone = $this->getClone();
        
//         func_get_args()
        $selectStatement = new SelectStatement($clone->modelName);
        $clone->select = $selectStatement->mount();
        $clone->from   = $this->tableName;
        
        return $clone;
    }
    
    function insert(array $attributes) {
        $clone = $this->getClone();
        
        $insertIntoStatement = new InsertIntoStatement($clone->modelName, $attributes);
        $clone->insertInto = $insertIntoStatement->getInsertIntoStatement();
        $clone->values = $insertIntoStatement->getValuesStatement();
        
        return $clone;
    }
    
//     to update a associatedTable, first delete all associated data, then insert it again
    function update($primaryKeyValue, array $attributes) {
        $clone = $this->getClone();
        
        $updateStatement = new UpdateStatement($clone->modelName, $primaryKeyValue, $attributes);
        $clone->update = $updateStatement->getUpdateStatement();
        $clone->set = $updateStatement->getSetStatement();
        $clone->whereClause = $updateStatement->getWhereStatement();
        
        return $clone;
    }
    
    function delete($columnName, ...$values) {
        $clone = $this->getClone();
        
        $deleteStatement = new DeleteStatement($clone->modelName, $columnName, ...$values);
        $clone->deleteFrom = $deleteStatement->getDeleteFromStatement();
        $clone->whereClause = $deleteStatement->getWhereClauseStatement();
        
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
                        $columnName = $this->primaryKey;
                    }
                } else {
                    if ($this->isContainedSet->offsetExists($joinedModelName)) {
                        $tableName = $this->tableName;
                        $columnName = $this->isContainedSet->offsetGet($joinedModelName)['associatedColumn'];
                    } else if (array_key_exists($this->associatedModelName, $this->isContainedSet)) {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->associatedModel::getPrimaryKey();
                    } else if (isset($this->associativeModelName)) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel::getForeignKeyOf($joinedModelName);
                    } else {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->containsSet->offsetGet($this->associatedModelName)['associatedColumn'];
                    }
                }
                
                array_push($map['joins'],
                    "$joinedTableName ON $joinedTableName.$joinedColumnName = $tableName.$columnName");
            }
        }
        
        if (isset($this->insertInto)) {
            $map['insertInto'] = $this->insertInto;
        }
        
        if (isset($this->values)) {
            $map['values'] = $this->values;
        }
        
        if (isset($this->update)) {
            $map['update'] = $this->update;
        }
        
        if (isset($this->set)) {
            $map['set'] = $this->set;
        }
        
        if (isset($this->deleteFrom)) {
            $map['deleteFrom'] = $this->deleteFrom;
        }
        
        if (isset($this->whereClause)) {
            $map['where'] = $this->whereClause;
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
