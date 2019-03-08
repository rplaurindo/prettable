<?php

namespace PReTTable\Repository;

use
    PReTTable,
    Exception
;

abstract class AbstractModel extends PReTTable\AbstractModel {
    
    protected $model;
    
    protected $tableName;
    
    protected $relationshipBuilding;
    
    protected $relationalSelectBuilding;

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);
        
        $this->relationshipBuilding = new RelationshipBuilding($this->modelName);
        $this->relationalSelectBuilding = new RelationalSelectBuilding($this->relationshipBuilding);
        
        $this->model = $this->relationshipBuilding->getModel();
        $this->tableName = $this->relationshipBuilding->getTableName();
    }
    
    function contains($modelName, $associatedColumn) {
        $this->relationshipBuilding->contains($modelName, $associatedColumn);
    }
    
    function isContained($modelName, $associatedColumn) {
        $this->relationshipBuilding->isContained($modelName, $associatedColumn);
    }
    
    function containsThrough($modelName, $through) {
        $this->relationshipBuilding->containsThrough($modelName, $through);
    }
    
    function join($modelName, $associatedColumn) {
        $clone = $this->getClone();
        
        $clone->relationalSelectBuilding->join($modelName, $associatedColumn);
        
        $clone->relationalSelectBuilding->addsInvolved($modelName);
        
        return $clone;
    }
    
    function getOrderBy() {
        if (isset($this->orderBy)) {
            
            if (count($this->getInvolvedTableNames())) {
                $explodedOrderByStatement = explode('.', $this->orderBy);
                
                if (count($explodedOrderByStatement) != 2
                    || !in_array($explodedOrderByStatement[0], $this->getInvolvedTableNames())
                    ) {
                        throw new Exception("The defined column of \"ORDER BY\" statement must be fully qualified containing " . implode(' or ', $this->getInvolvedTableNames()));
                    }
            }
            
            return "
                ORDER BY $this->orderBy $this->orderOfOrderBy";
        }
        
        return null;
    }

}
