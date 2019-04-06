<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    PReTTable,
    PReTTable\InheritanceRelationship,
    PReTTable\QueryStatements\Component,
    PReTTable\QueryStatements\Decorators\Select,
    PReTTable\Reflection
;

abstract class AbstractModelBase extends PReTTable\AbstractModel {

    private $setOfThoseContained;

    private $setOfContains;
    
    private $associativeModels;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->setOfThoseContained = new ArrayObject();
        $this->setOfContains = new ArrayObject();
        $this->associativeModels = new ArrayObject();
    }

    function join($modelName, $type = 'INNER', $leftModelName = null) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        if (
                (
                    $this->doesItContain($modelName)
                    || $this->isItContained($modelName)
                )
                || ($modelName == $this->name && isset($leftModelName))
            )
        {
            if ($modelName == $this->name && isset($leftModelName)) {
                $leftColumnName = $this->getAssociatedColumn($modelName);
                $columnName = $this->getAssociatedColumn($leftModelName);
                
                if ($this->doesItContainThrough($modelName)) {
                    $leftModelName = $this->getAssociativeModelNameFrom($modelName);
                    $this->addsInvolvedTable($leftModelName);
                }
            } else {
                $associativeModelName = $this->getAssociativeModelNameFrom($leftModelName);
                $leftModelName = $associativeModelName;
                
                $leftColumnName = $this->getAssociatedColumn($modelName);
                $columnName = $this->getAssociatedColumn($leftModelName);
            }
            
            parent::join($modelName, $columnName, $leftColumnName, $type, $leftModelName);
        }
        
    }

    protected function contains($modelName, $associatedColumn) {
        InheritanceRelationship::throwIfClassIsnotA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');

        $this->setOfThoseContained
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }

    protected function containsThrough($modelName, $through) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');

        $this->setOfThoseContained
            ->offsetSet($modelName, ['associativeModelName' => $through]);
        
        $this->associativeModels->offsetSet($modelName, $through);
    }

    protected function isContained($modelName, $associatedColumn) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            'PReTTable\IdentifiableModelInterface');

        $this->setOfContains
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }

    protected function getAssociativeModelNameFrom($modelName) {
        if (is_subclass_of($modelName, 'PReTTable\AssociativeModelInterface')) {
            foreach ($this->associativeModels as $currentModelName => $associativeModel) {
                if ($currentModelName == $modelName) {
                    return $associativeModel;
                }
            }
        } else if ($this->associativeModels->offsetExists($modelName)) {
            return $this->associativeModels->offsetGet($modelName);
        }
        
        return null;
    }

    protected function build($modelName) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');
        
        $clone = $this->getClone();

        if ($clone->doesItContain($modelName)
            || $clone->isItContained($modelName)) {

            $associatedModel = Reflection::getDeclarationOf($modelName);
            $associatedTableName = $associatedModel->getTableName();
            $associatedColumnName = $clone->getAssociatedColumn($modelName);
            $select = new Select($this);

            $fromStatement = $associatedTableName;
//             s� falta consertar isso
            $queryStatement = "
            SELECT $select->getStatement(...$clone->getInvolvedModelNames());
            
            FROM $fromStatement";
                
            $clone->queryComponent = new Component($queryStatement);
            
            if ($clone->isItContained($modelName)) {
                $clone->join($clone->name, 'INNER', $modelName);
            } else if ($clone->doesItContainThrough($modelName)) {
                $associativeModelName = $this
                    ->getAssociativeModelNameFrom($modelName);
                
                $queryStatement = "
                SELECT $select->getStatement(...$clone->getInvolvedModelNames());
                
                FROM $fromStatement";
                
                $clone->queryComponent = new Component($queryStatement);

                $associativeModel = Reflection
                    ::getDeclarationOf($associativeModelName);

                $associativeTableName = $associativeModel->getTableName();
                $fromStatement = $associativeTableName;

                $clone->join($clone->name, 'INNER', $associativeModelName);

                $clone->join($modelName);
            } else {
                $clone->join($modelName);
            }
        }
    }
    
    private function doesItContain($modelName) {
        return $this->setOfThoseContained->offsetExists($modelName);
    }
    
    private function doesItContainThrough($modelName) {
        return ($this->setOfThoseContained->offsetExists($modelName)
            && array_key_exists('associativeModelName',
                $this->setOfThoseContained->offsetGet($modelName)));
    }
    
    private function isItContained($modelName) {
        return $this->setOfContains->offsetExists($modelName);
    }
    
    private function getAssociatedColumn($modelName) {
        if (($this->doesItContain($modelName)
            || $this->isItContained($modelName))
            ) {
            if ($this->isItContained($modelName)) {
                return $this->setOfContains
                    ->offsetGet($modelName)['associatedColumn'];
            } else if (!$this->doesItContainThrough($modelName)) {
                $associativeModelName = $this
                    ->getAssociativeModelNameFrom($modelName);
                $associativeModel = Reflection
                    ::getDeclarationOf($associativeModelName);
                return $associativeModel->getAssociativeKeys()[$modelName];
            } else {
                return $this->setOfThoseContained
                    ->offsetGet($modelName)['associatedColumn'];
            }
        }
        
        return null;
    }

}
