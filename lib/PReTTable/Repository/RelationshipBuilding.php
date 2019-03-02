<?php

namespace PReTTable\Repository;

use
    Exception,
    ArrayObject,
    PReTTable\Reflection
;

class RelationshipBuilding {

    private $modelName;

    private $model;

    private $tableName;

    private $primaryKeyName;

    private $primaryKeyValue;

    private $setOfThoseContained;

    private $setOfContains;

    function __construct($modelName) {
        self::checkIfModelIs($modelName,
            'PReTTable\IdentifiableModelInterface');

        $this->modelName = $modelName;

        $this->model = Reflection::getDeclarationOf($modelName);
        $this->tableName = self::resolveTableName($modelName);
        $this->primaryKeyName = $this->model->getPrimaryKeyName();
        $this->primaryKeyValue = null;

        $this->setOfThoseContained = new ArrayObject();
        $this->setOfContains = new ArrayObject();
    }

    static function resolveTableName($modelName) {
        $model = Reflection::getDeclarationOf($modelName);

        $tableName = $model::getTableName();
        if (empty ($tableName)) {
            return $modelName;
        }

        return $tableName;
    }

    static function checkIfModelIs($modelName, ...$classes) {
        $count = 0;

        foreach ($classes as $class) {
            if (is_subclass_of($modelName, $class)) {
                $count++;
            }
        }

        if (!$count) {
            $classesAsText = implode(" or ", $classes);
            throw new Exception("The model must be a $classesAsText");
        }

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

    function setPrimaryKeyValue($value) {
        $this->primaryKeyValue = $value;
    }

    function getPrimaryKeyValue() {
        return $this->primaryKeyValue;
    }

    function contains($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');

        $this->setOfThoseContained
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }

    function isItContained($modelName) {
        return $this->setOfThoseContained->offsetExists($modelName);
    }

    function containsThrough($modelName, $through) {
        self::checkIfModelIs($modelName,
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
        self::checkIfModelIs($modelName,
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
