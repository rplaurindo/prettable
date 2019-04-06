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

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->setOfThoseContained = new ArrayObject();
        $this->setOfContains = new ArrayObject();
    }

    function join($modelName, $columnName, $type = 'INNER') {
        InheritanceRelationship::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        if ($this->doesItContain($modelName)
            || $this->isItContained($modelName)
            ) {
            $model = Reflection::getDeclarationOf($modelName);
            $tableName = $model::getTableName();
            
            $leftModelName = $this->name;
            $leftColumnName = $this->getAssociatedColumn($modelName);
            if ($this->doesItContainThrough($modelName)) {
                $leftModelName = $this->getAssociativeModelNameOf($modelName);
            }
        }
        
        parent::join($modelName, $columnName, $leftColumnName, $type, $leftModelName);
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
        if ($this->doesItContainThrough($modelName)
            ) {
            return $this->setOfThoseContained
                ->offsetGet($modelName)['associativeModelName'];
        }

        return null;
    }

    protected function build($modelName) {
        InheritanceRelationship::checkIfClassIsA($modelName,
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
                
                $queryStatement = "
                SELECT $select->getStatement(...$clone->getInvolvedModelNames());
                
                FROM $fromStatement";
                
                $clone->queryComponent = new Component($queryStatement);

                if ($clone->doesItContainThrough($modelName)) {

                    $associativeModelName = $this
                        ->getAssociativeModelNameOf($modelName);

                    $clone->addsInvolvedModel($associativeModelName);
                    
                    $queryStatement = "
                    SELECT $select->getStatement(...$clone->getInvolvedModelNames());
                    
                    FROM $fromStatement";
                    
                    $clone->queryComponent = new Component($queryStatement);

                    $associativeModel = Reflection
                        ::getDeclarationOf($associativeModelName);

                    $associativeTableName = $associativeModel
                        ->getTableName();
                    $fromStatement = $associativeTableName;


                    $associativeColumnOfModel = $associativeModel
                        ->getAssociativeColumnNames()[$clone->name];

//                     TODO: improve and test it
                    $clone->join($clone->name, $clone->getPrimaryKeyName(),
                        $associativeColumnOfModel, 'INNER',
                        $associativeModelName
                    );


                    $associativeColumnOfAssociatedModel = $associativeModel
                        ->getAssociativeColumnNames()[$modelName];

//                     TODO: improve and test it
                    $clone->join($modelName,
                        $associatedModel->getPrimaryKeyName(),
                        $associativeColumnOfAssociatedModel, 'INNER',
                        $associativeModelName
                    );
                } else if ($clone->doesItContain($modelName)) {
//                     TODO: improve and test it
                    $clone->join($clone->name, $associatedColumnName,
                        $clone->getPrimaryKeyName(), 'INNER', $modelName);
                } else {
//                     TODO: improve and test it
                    $clone->join($clone->name, $clone->getPrimaryKeyName(),
                        $associatedColumnName, 'INNER', $modelName);
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
            if ($this->doesItContain($modelName)) {
                if (!$this->doesItContainThrough($modelName)) {
                    $associativeModelName = $this
                        ->getAssociativeModelNameOf($modelName);
                    $associativeModel = Reflection
                        ::getDeclarationOf($associativeModelName);
                    return $associativeModel->getAssociativeKeys()[$modelName];
                } else {
                    return $this->setOfThoseContained
                        ->offsetGet($modelName)['associatedColumn'];
                }
            } else {
                return $this->setOfContains
                    ->offsetGet($modelName)['associatedColumn'];
            }
        }
        
        return null;
    }

}
