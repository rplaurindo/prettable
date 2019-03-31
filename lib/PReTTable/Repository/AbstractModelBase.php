<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    Exception,
    PReTTable,
    PReTTable\InheritanceRelationship,
    PReTTable\QueryStatements\Select,
    PReTTable\Reflection
;

abstract class AbstractModelBase extends PReTTable\AbstractModel {
    
    private $setOfThoseContained;
    
    private $setOfContains;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->setOfThoseContained = new ArrayObject();
        $this->setOfContains = new ArrayObject();
    }
    
    protected function contains($modelName, $associatedColumn) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');
        
        $this->setOfThoseContained
        ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    protected function isItContained($modelName) {
        return $this->setOfThoseContained->offsetExists($modelName);
    }
    
    protected function containsThrough($modelName, $through) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');
        
        $this->setOfThoseContained
        ->offsetSet($modelName, ['associativeModelName' => $through]);
    }
    
    protected function getAssociativeModelNameOf($modelName) {
        if ($this->isItContained($modelName)) {
            $relationshipData = $this->setOfThoseContained
            ->offsetGet($modelName);
            
            if ($this->isItContainedThrough($modelName)) {
                return $relationshipData['associativeModelName'];
            }
        }
        
        return null;
    }
    
    protected function getAssociatedColumn($modelName) {
        if (($this->isItContained($modelName)
            || $this->doesItContain($modelName))
            && !$this->isItContainedThrough($modelName)) {
                if ($this->isItContained($modelName)) {
                    return $this->setOfThoseContained
                    ->offsetGet($modelName)['associatedColumn'];
                } else {
                    return $this->setOfContains
                    ->offsetGet($modelName)['associatedColumn'];
                }
            }
            
            return null;
    }
    
    protected function build($modelName) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');
        
        $clone = $this->getClone();
        
        if ($clone->isItContained($modelName)
            || $clone->doesItContain($modelName)) {
                
                $associatedModel = Reflection::getDeclarationOf($modelName);
                $associatedTableName = $associatedModel->getTableName();
//                 usar como strategy
                $selectStatement = new Select($modelName);
                $fromStatement = $associatedTableName;
                
                if ($clone->isItContained($modelName)) {
                    
                    if ($clone->isItContainedThrough($modelName)) {
                        
                        $associativeModelName = $clone
                            ->getAssociativeModelNameOf($modelName);
                        
                        $this->addsInvolvedModel($associativeModelName);
                        
                        $associativeModel = Reflection
                            ::getDeclarationOf($associativeModelName);
                        
                        $associativeTableName = $associativeModel
                            ->getTableName();
                        $fromStatement = $associativeTableName;
                        
                        
                        $associativeColumnOfModel = $associativeModel
                            ->getAssociativeColumnNames()[$clone->name];
                        
                        $clone->join($clone->name, $clone->getPrimaryKeyName(),
                            $associativeColumnOfModel, 'INNER',
                            $associativeModelName);
                        
                        
                        $associativeColumnOfAssociatedModel = $associativeModel
                            ->getAssociativeColumnNames()[$modelName];
                        
                        $clone->join($modelName,
                            $associatedModel->getPrimaryKeyName(),
                            $associativeColumnOfAssociatedModel, 'INNER',
                            $associativeModelName);
                    } else {
                        $associatedColumn = $clone->getAssociatedColumn($modelName);

                        $clone->join($clone->name, $clone->getPrimaryKeyName(),
                            $associatedColumn, 'INNER',
                            $modelName);
                    }
                } else {
                    //                 $associatedColumn = $clone->relationshipBuilding
//                     $associatedColumn = $clone->getAssociatedColumn($modelName);
                    
                    //                 $clone->join($clone->modelName, $associatedColumn);
                }
                
//                 $clone->selectStatement = $clone->select
//                     ->getStatement(true, ...$clone->getInvolvedModelNames());
            }
            
            echo $clone->mountJoinsStatement();
            
            return $clone;
    }

    protected function getOrderBy() {
        if (isset($this->orderBy)) {

            if (count($this->getInvolvedTableNames())) {
                $explodedOrderByStatement = explode('.', $this->orderBy);

                if (count($explodedOrderByStatement) != 2
                    || !in_array($explodedOrderByStatement[0],
                        $this->getInvolvedTableNames())
                    ) {
                        throw new Exception("The defined column of \"ORDER BY\" statement must be fully qualified containing " . implode(' or ', $this->getInvolvedTableNames()));
                    }
            }

            return "
            ORDER BY $this->orderBy $this->orderOfOrderBy";
        }

        return null;
    }
    
    private function isItContainedThrough($modelName) {
        return ($this->setOfThoseContained->offsetExists($modelName)
            && array_key_exists('associativeModelName',
                $this->setOfThoseContained->offsetGet($modelName)));
    }
    
    private function isContained($modelName, $associatedColumn) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface');
        
        $this->setOfContains
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    private function doesItContain($modelName) {
        return $this->setOfContains->offsetExists($modelName);
    }

}
