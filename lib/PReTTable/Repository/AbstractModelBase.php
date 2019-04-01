<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    Exception,
    PReTTable,
    PReTTable\InheritanceRelationship,
    PReTTable\Query,
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
    
    protected function containsThrough($modelName, $through) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');
        
        $this->setOfThoseContained
            ->offsetSet($modelName, ['associativeModelName' => $through]);
    }
    
    protected function isContained($modelName, $associatedColumn) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface');
        
        $this->setOfContains
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
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
        
        $query = new Query();
        
        if ($clone->isItContained($modelName)
            || $clone->doesItContain($modelName)) {
                
                $associatedModel = Reflection::getDeclarationOf($modelName);
                $associatedTableName = $associatedModel->getTableName();
                $associatedColumnName = $clone->getAssociatedColumn($modelName);
                $select = new Select($modelName);
                
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
                        $clone->join($clone->name, $clone->getPrimaryKeyName(),
                            $associatedColumnName, 'INNER', $modelName);
                    }
                } else {
                    $clone->join($clone->name, $associatedColumnName,
                        $clone->getPrimaryKeyName(), 'INNER', $modelName);
                }
                
                $query->setSelectStatement($select
                    ->getStatement(...$clone->getInvolvedModelNames()));
                $query->setFromStatement($fromStatement);
            }
            
            return $query;
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

            return "\n\tORDER BY $this->orderBy $this->orderOfOrderBy\n";
        }

        return null;
    }
    
    private function isItContained($modelName) {
        return $this->setOfThoseContained->offsetExists($modelName);
    }
    
    private function isItContainedThrough($modelName) {
        return ($this->setOfThoseContained->offsetExists($modelName)
            && array_key_exists('associativeModelName',
                $this->setOfThoseContained->offsetGet($modelName)));
    }
    
    private function doesItContain($modelName) {
        return $this->setOfContains->offsetExists($modelName);
    }

}
