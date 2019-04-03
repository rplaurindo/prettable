<?php

namespace PReTTable\Repository;

use
    ArrayObject,
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

//         retornar um SelectComponent ao invés de um objeto Query
        $query = new Query();

        if ($this->isItContained($modelName)
            || $this->doesItContain($modelName)) {

                $associatedModel = Reflection::getDeclarationOf($modelName);
                $associatedTableName = $associatedModel->getTableName();
                $associatedColumnName = $this->getAssociatedColumn($modelName);
                $select = new Select($this);

                $fromStatement = $associatedTableName;

                if ($this->isItContained($modelName)) {

                    if ($this->isItContainedThrough($modelName)) {

                        $associativeModelName = $this
                            ->getAssociativeModelNameOf($modelName);

                        $this->addsInvolvedModel($associativeModelName);

                        $associativeModel = Reflection
                            ::getDeclarationOf($associativeModelName);

                        $associativeTableName = $associativeModel
                            ->getTableName();
                        $fromStatement = $associativeTableName;


                        $associativeColumnOfModel = $associativeModel
                            ->getAssociativeColumnNames()[$this->name];

                        $this->join($this->name, $this->getPrimaryKeyName(),
                            $associativeColumnOfModel, 'INNER',
                            $associativeModelName);


                        $associativeColumnOfAssociatedModel = $associativeModel
                            ->getAssociativeColumnNames()[$modelName];

                        $this->join($modelName,
                            $associatedModel->getPrimaryKeyName(),
                            $associativeColumnOfAssociatedModel, 'INNER',
                            $associativeModelName);
                    } else {
                        $this->join($this->name, $this->getPrimaryKeyName(),
                            $associatedColumnName, 'INNER', $modelName);
                    }
                } else {
                    $this->join($this->name, $associatedColumnName,
                        $this->getPrimaryKeyName(), 'INNER', $modelName);
                }

                $query->setSelectStatement($select
                    ->getStatement(...$this->getInvolvedModelNames()));
                $query->setFromStatement($fromStatement);
            }

            return $query;
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
