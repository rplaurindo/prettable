<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    PReTTable,
    PReTTable\InheritanceRelationship,
//     PReTTable\Query,
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
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

        if ($clone->isItContained($modelName)
            || $clone->doesItContain($modelName)) {

                $associatedModel = Reflection::getDeclarationOf($modelName);
                $associatedTableName = $associatedModel->getTableName();
                $associatedColumnName = $clone->getAssociatedColumn($modelName);
                $select = new Select($this);

                $fromStatement = $associatedTableName;
                
                $queryStatement = "
                SELECT $select->getStatement(...$clone->getInvolvedModelNames());
                
                FROM $fromStatement";
                
                $clone->queryComponent = new SelectComponent($queryStatement);

                if ($clone->isItContained($modelName)) {

                    if ($clone->isItContainedThrough($modelName)) {

                        $associativeModelName = $this
                            ->getAssociativeModelNameOf($modelName);

                        $clone->addsInvolvedModel($associativeModelName);
                        
                        $queryStatement = "
                        SELECT $select->getStatement(...$clone->getInvolvedModelNames());
                        
                        FROM $fromStatement";
                        
                        $clone->queryComponent = new SelectComponent($queryStatement);

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
            }
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
