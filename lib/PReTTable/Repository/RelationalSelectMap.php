<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    PReTTable\Reflection,
    PReTTable\QueryStatements\Select
;

// a layer to mount a map of queries to select data
class RelationalSelectMap {
    
    protected $relationshipMap;
    
    protected $modelName;
    
    protected $tableName;
    
    protected $primaryKeyName;
    
    protected $associatedModelName;
    
    protected $associatedModel;
    
    protected $associatedTableName;
    
    protected $associativeModelName;
    
    protected $associativeTableName;
    
    protected $associativeModel;
    
    protected $involvedModelNames;
    
    protected $select;
    
    protected $from;
    
    protected $joins;
    
    protected $whereClause;

    function __construct(RelationshipMap $relationshipMap) {
        $this->relationshipMap = $relationshipMap;
        
        $this->modelName = $relationshipMap->getModelName();
        $model = Reflection::getDeclarationOf($this->modelName);
        $this->tableName = self::resolveTableName($this->modelName);
        $this->primaryKeyName = $model->getPrimaryKeyName();
        
        $this->joins = new ArrayObject();
        
        $this->involvedModelNames = new ArrayObject();
    }
    
    function select($modelName) {
        $functionArguments = func_get_args();
        
        if (count($functionArguments) == 2) {
            $primaryKeyValue = $modelName;
            $modelName = $functionArguments[1];
        }
        
        self::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        $clone = $this->getClone();
        
        $clone->associatedModelName = $modelName;
        $clone->associatedModel = Reflection::getDeclarationOf($modelName);
        $clone->associatedTableName = self::resolveTableName($modelName);
        
        $clone->select = new Select();
        $clone->involvedModelNames->append($modelName);
        
        if ($clone->containsSet->offsetExists($modelName)
            || $clone->isContainedSet2->offsetExists($modelName)) {
                $clone->from = $clone->associatedTableName;
                
                if ($clone->containsSet->offsetExists($modelName)) {
                    if (array_key_exists('associativeModelName',
                        $clone->containsSet->offsetGet($modelName))
                        ) {
                            
                            $clone->associativeModelName = $clone
                                ->getAssociativeModelNameOf($modelName);
                            
                            $clone->involvedModelNames->append($clone->associativeModelName);
                            
                            $clone->associativeModel = Reflection
                                ::getDeclarationOf($clone->associativeModelName);
                            
                            $clone->associativeTableName = self
                                ::resolveTableName($clone->associativeModelName);
                            $clone->from = $clone->associativeTableName;
                            
                            $clone->join($clone->modelName, $clone->primaryKeyName);
                            $clone->join($modelName,
                                $clone->associatedModel->getPrimaryKeyName());
                            
                            $associativeColumn = $clone->associativeModel->getAssociativeKeys()[$clone->modelName];
                            if (isset($primaryKeyValue)) {
                                $clone->whereClause = "$clone->associativeTableName.$associativeColumn = $primaryKeyValue";
                            }
                        } else {
                            $clone->join($clone->modelName, $clone->primaryKeyName);
                            
                            $associatedColumn = $clone->containsSet
                                ->offsetGet($modelName)['associatedColumn'];
                            
                            if (isset($primaryKeyValue)) {
                                $clone->whereClause = "$clone->associatedTableName.$associatedColumn = $primaryKeyValue";
                            }
                        }
                } else {
                    $associatedColumn = $clone->isContainedSet2
                        ->offsetGet($modelName)['associatedColumn'];
                    
                    $clone->join($clone->modelName, $associatedColumn);
                    
                    if (isset($primaryKeyValue)) {
                        $clone->whereClause = "$clone->tableName.$associatedColumn = $primaryKeyValue";
                    }
                }
            }
            
            return $clone;
    }
    
    function join($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        $clone = $this->getClone();
        
        if (!$clone->joins->offsetExists($modelName)
            && ($clone->containsSet->offsetExists($modelName)
                || $clone->isContainedSet2->offsetExists($modelName))
            || $modelName == $clone->modelName
            ) {
                $clone->joins->offsetSet($modelName, $associatedColumn);
            }
            
            return clone $clone;
    }
    
    function getInvolvedModelNames() {
        return $this->involvedModelNames->getArrayCopy();
    }
    
    function getMap() {
        $map = [];
        
        if (isset($this->select)) {
            $map['select'] = $this->select
            ->getStatement(true, ...$this->getInvolvedModelNames());
        }
        
        if (isset($this->from)) {
            $map['from'] = $this->from;
        }
        
        if ($this->joins->count()) {
            $map['joins'] = $this->getJoins();
        }
        
        if (isset($this->whereClause)) {
            $map['where'] = $this->whereClause;
        }
        
        return $map;
    }
    
    function getJoins() {
        $joins = [];
        
        foreach ($this->joins as $joinedModelName => $joinedColumnName) {
            $joinedTableName = self::resolveTableName($joinedModelName);
            
            if ($this->containsSet->offsetExists($joinedModelName)) {
                if (array_key_exists('associativeModelName', $this
                    ->containsSet->offsetGet($joinedModelName))) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel
                            ->getAssociativeKeys()[$joinedModelName];
                    } else {
                        $tableName = $this->tableName;
                        $columnName = $this->primaryKeyName;
                    }
            } else {
                if ($this->isContainedSet2->offsetExists($joinedModelName)) {
                    $tableName = $this->tableName;
                    $columnName = $this->isContainedSet2
                        ->offsetGet($joinedModelName)['associatedColumn'];
                } else if (array_key_exists($this->associatedModelName,
                    $this->isContainedSet2)) {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->associatedModel->getPrimaryKeyName();
                    } else if (isset($this->associativeModelName)) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel
                            ->getAssociativeKeys()[$joinedModelName];
                    } else {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->containsSet->offsetGet($this
                            ->associatedModelName)['associatedColumn'];
                    }
            }
            
            array_push($joins,
                "$joinedTableName ON $joinedTableName.$joinedColumnName = $tableName.$columnName\n");
        }
        
        return $joins;
    }
    
    protected function getClone() {
        return clone $this;
    }
    
}
