<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    PReTTable\InheritanceRelationship,
    PReTTable\Reflection
;

class RelationshipBuilding {

    private $modelName;

    private $model;

    private $tableName;

    private $primaryKeyName;

    private $setOfThoseContained;

    private $setOfContains;

    function __construct($modelName) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface');

        $this->modelName = $modelName;

        $this->model = Reflection::getDeclarationOf($modelName);
        $this->tableName = $this->model->getTableName();
        $this->primaryKeyName = $this->model->getPrimaryKeyName();

        $this->setOfThoseContained = new ArrayObject();
        $this->setOfContains = new ArrayObject();
    }

    function getModelName() {
        return $this->modelName;
    }

    function getModel() {
        return $this->model;
    }

    function getTableName() {
        return $this->tableName;
    }

    function getPrimaryKeyName() {
        return $this->primaryKeyName;
    }

    function contains($modelName, $associatedColumn) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');

        $this->setOfThoseContained
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }

    function isItContained($modelName) {
        return $this->setOfThoseContained->offsetExists($modelName);
    }

    function containsThrough($modelName, $through) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');

        $this->setOfThoseContained->offsetSet($modelName, ['associativeModelName' => $through]);
    }

    function isItContainedThrough($modelName) {
        return ($this->setOfThoseContained->offsetExists($modelName)
            && array_key_exists('associativeModelName',
                $this->setOfThoseContained->offsetGet($modelName)));
    }

    function isContained($modelName, $associatedColumn) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface');

        $this->setOfContains
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }

    function doesItContain($modelName) {
        return $this->setOfContains->offsetExists($modelName);
    }

    function getAssociativeModelNameOf($modelName) {
        if ($this->isItContained($modelName)) {
            $relationshipData = $this->setOfThoseContained->offsetGet($modelName);

            if ($this->isItContainedThrough($modelName)) {
                return $relationshipData['associativeModelName'];
            }
        }

        return null;
    }

    function getAssociatedColumn($modelName) {
        if (($this->isItContained($modelName) || $this->doesItContain($modelName))
            && !$this->isItContainedThrough($modelName)) {
            if ($this->isItContained($modelName)) {
                return $this->setOfThoseContained->offsetGet($modelName)['associatedColumn'];
            } else {
                return $this->setOfContains->offsetGet($modelName)['associatedColumn'];
            }
        }

        return null;
    }

}
