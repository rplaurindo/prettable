<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    PReTTable\Reflection,
    PReTTable\QueryStatements\Select
;

// a layer to mount a map of queries to select data
class RelationalSelectBuilding {
    
    private $relationshipBuilding;
    
    private $modelName;
    
    private $tableName;
    
    private $primaryKeyName;
    
    private $associatedModelName;
    
    private $associatedModel;
    
    private $associatedTableName;
    
    private $associativeModelName;
    
    private $associativeTableName;
    
    private $associativeModel;
    
    private $involvedModelNames;
    
    private $select;
    
    private $selectStatement;
    
    private $fromStatement;
    
    private $joins;
    
    private $whereClauseStatement;

    function __construct(RelationshipBuilding $relationshipBuilding) {
        $this->relationshipBuilding = $relationshipBuilding;
        
        $this->modelName = $relationshipBuilding->getModelName();
        $this->tableName = $relationshipBuilding->getTableName();
        $this->primaryKeyName = $relationshipBuilding->getPrimaryKeyName();
        
        $this->joins = new ArrayObject();
        
        $this->involvedModelNames = new ArrayObject();
        
        $this->select = new Select();
    }
    
    function getSelect() {
        return $this->selectStatement;
    }
    
    function getFrom() {
        return $this->fromStatement;
    }
    
    function getWhereClause() {
        return $this->whereClauseStatement;
    }
    
    function build($modelName) {
        RelationshipBuilding::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        $clone = $this->getClone();
        
        $primaryKeyValue = $clone->relationshipBuilding->getPrimaryKeyValue();
        
        $clone->associatedModelName = $modelName;
        $clone->associatedModel = Reflection::getDeclarationOf($modelName);
        $clone->associatedTableName = RelationshipBuilding::resolveTableName($modelName);
        
        $clone->addsInvolvedModelNames($modelName);
        
        if ($clone->relationshipBuilding->isItContained($modelName)
            || $clone->relationshipBuilding->doesItContain($modelName)) {
                $clone->fromStatement = $clone->associatedTableName;
                
                if ($clone->relationshipBuilding->isItContained($modelName)) {
                    if ($clone->relationshipBuilding
                        ->isItContainedThrough($modelName)) {
                            
                            $clone->associativeModelName = $clone
                                ->relationshipBuilding
                                ->getAssociativeModelNameOf($modelName);
                            
                            $clone->addsInvolvedModelNames($clone
                                ->associativeModelName);
                            
                            $clone->associativeModel = Reflection
                                ::getDeclarationOf($clone->associativeModelName);
                            
                            $clone->associativeTableName = RelationshipBuilding
                                ::resolveTableName($clone->associativeModelName);
                            $clone->fromStatement = $clone->associativeTableName;
                            
                            $clone->join($clone->modelName, $clone->primaryKeyName);
                            $clone->join($modelName, $clone->associatedModel
                                ->getPrimaryKeyName());                            
                            
                            $associativeColumn = $clone->associativeModel
                                ->getAssociativeKeys()[$clone->modelName];
                            if (isset($primaryKeyValue)) {
                                $clone->whereClauseStatement = "$clone->associativeTableName.$associativeColumn = $primaryKeyValue";
                            }
                        } else {
                            $clone->join($clone->modelName, $clone->primaryKeyName);
                            
                            $associatedColumn = $clone->relationshipBuilding->getAssociatedColumn($modelName);
                            
                            if (isset($primaryKeyValue)) {
                                $clone->whereClauseStatement = "$clone->associatedTableName.$associatedColumn = $primaryKeyValue";
                            }
                        }
                } else {
                    $associatedColumn = $clone->relationshipBuilding->getAssociatedColumn($modelName);
                    
                    $clone->join($clone->modelName, $associatedColumn);
                    
                    if (isset($primaryKeyValue)) {
                        $clone->whereClauseStatement = "$clone->tableName.$associatedColumn = $primaryKeyValue";
                    }
                }
            }
            
            $clone->selectStatement = $clone->select
                ->getStatement(true, ...$clone->getInvolvedModelNames());
            
            return $clone;
    }
    
    function join($modelName, $associatedColumn) {
        RelationshipBuilding::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        $clone = $this->getClone();
        
        if (!$clone->joins->offsetExists($modelName)
            && ($clone->relationshipBuilding->isItContained($modelName)
                || $clone->relationshipBuilding->doesItContain($modelName))
            || $modelName == $clone->modelName
            ) {
                $clone->joins->offsetSet($modelName, $associatedColumn);
            }
            
            return clone $clone;
    }
    
    function addsInvolvedModelNames($modelName) {
        $this->involvedModelNames->append($modelName);
    }
    
    function getInvolvedModelNames() {
        return $this->involvedModelNames->getArrayCopy();
    }
    
    function getJoins() {
        $joins = [];
        
        foreach ($this->joins as $joinedModelName => $joinedColumnName) {
            $joinedTableName = RelationshipBuilding::resolveTableName($joinedModelName);
            
            if ($this->relationshipBuilding->isItContained($joinedModelName)) {
                if ($this->relationshipBuilding
                    ->isItContainedThrough($joinedModelName)) {
                        $tableName = $this->associativeTableName;
                        $columnName = $this->associativeModel
                            ->getAssociativeKeys()[$joinedModelName];
                    } else {
                        $tableName = $this->tableName;
                        $columnName = $this->primaryKeyName;
                    }
            } else {
                if ($this->relationshipBuilding->doesItContain($joinedModelName)) {
                    $tableName = $this->tableName;
                    $columnName = $this->relationshipBuilding->getAssociatedColumn($joinedModelName);
                } else if ($this->relationshipBuilding->doesItContain($this->associatedModelName)) {
                    $tableName = $this->associatedTableName;
                    $columnName = $this->associatedModel->getPrimaryKeyName();
                } else if (isset($this->associativeModelName)) {
                    $tableName = $this->associativeTableName;
                    $columnName = $this->associativeModel
                        ->getAssociativeKeys()[$joinedModelName];
                } else {
                    $tableName = $this->associatedTableName;
                    $columnName = $this->relationshipBuilding->getAssociatedColumn($this->associatedModelName);
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
