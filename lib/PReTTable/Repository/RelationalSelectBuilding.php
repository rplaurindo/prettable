<?php

namespace PReTTable\Repository;

use
    ArrayObject,
    PReTTable\AbstractModel,
    PReTTable\InheritanceRelationship,
    PReTTable\QueryStatements\Select,
    PReTTable\Reflection
;

// a layer to mount a map of queries to select data
class RelationalSelectBuilding {

    private $relationshipBuilding;

    private $model;

    private $modelName;

    private $tableName;

    private $primaryKeyName;

    private $associatedModelName;

    private $associatedModel;

    private $associatedTableName;

    private $associativeModelName;

    private $associativeTableName;

    private $associativeModel;

    private $select;

    private $selectStatement;

    private $fromStatement;

    private $joins;

    private $whereClauseStatement;

    function __construct(AbstractModel $model, RelationshipBuilding $relationshipBuilding) {
        $this->model = $model;
        $this->modelName = $this->model->getName();
        $this->tableName = $this->model->getTableName();
        $this->primaryKeyName = $this->model->getPrimaryKeyName();

        $this->relationshipBuilding = $relationshipBuilding;

        $this->joins = new ArrayObject();
    }

    function join($modelName, $associatedColumn) {
       InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');

        $clone = $this->getClone();

        if (!$clone->joins->offsetExists($modelName)) {
            $clone->joins->offsetSet($modelName, $associatedColumn);
        }

        return clone $clone;
    }

    function build($modelName, $primaryKeyValue = null) {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\IdentifiableModelInterface',
            'PReTTable\AssociativeModelInterface');

        $clone = $this->getClone();

        if ($clone->relationshipBuilding->isItContained($modelName)
            || $clone->relationshipBuilding->doesItContain($modelName)) {

            $clone->associatedModelName = $modelName;
            $clone->associatedModel = Reflection::getDeclarationOf($modelName);
            $clone->associatedTableName = $clone->associatedModel->getTableName();

            $clone->select = new Select\Repository($clone->associatedModelName);
            $clone->fromStatement = $clone->associatedTableName;

            if ($clone->relationshipBuilding->isItContained($modelName)) {

                if ($clone->relationshipBuilding
                    ->isItContainedThrough($modelName)) {

                    $clone->associativeModelName = $clone
                        ->relationshipBuilding
                        ->getAssociativeModelNameOf($modelName);

                    $clone->model->addsInvolvedModel($clone->associativeModelName);

                    $clone->associativeModel = Reflection
                        ::getDeclarationOf($clone->associativeModelName);

                    $clone->associativeTableName = $clone->associativeModel->getTableName();
                    $clone->fromStatement = $clone->associativeTableName;

                    $clone->join($clone->modelName, $clone->primaryKeyName);
                    $clone->join($modelName, $clone->associatedModel
                        ->getPrimaryKeyName());

                    $associativeColumn = $clone->associativeModel
                        ->getAssociativeColumnNames()[$clone->modelName];

                    if (isset($primaryKeyValue)) {
                        $clone->whereClauseStatement = "$clone->associativeTableName.$associativeColumn = $primaryKeyValue";
                    }
                } else {
                    $clone->join($clone->modelName, $clone->primaryKeyName);

                    $associatedColumn = $clone->relationshipBuilding
                        ->getAssociatedColumn($modelName);

                    if (isset($primaryKeyValue)) {
                        $clone->whereClauseStatement = "$clone->associatedTableName.$associatedColumn = $primaryKeyValue";
                    }
                }

            } else {
                $associatedColumn = $clone->relationshipBuilding
                    ->getAssociatedColumn($modelName);

                $clone->join($clone->modelName, $associatedColumn);

                if (isset($primaryKeyValue)) {
                    $clone->whereClauseStatement = "$clone->tableName.$clone->primaryKeyName = $primaryKeyValue";
                }
            }

            $clone->selectStatement = $clone->select
                ->getStatement(true, ...$clone->model->getInvolvedModelNames());
        }

        return $clone;
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

    function getJoins() {
        $joins = [];

        foreach ($this->joins as $joinedModelName => $joinedColumnName) {
            $joinedModel = Reflection::getDeclarationOf($joinedModelName);
            $joinedTableName = $joinedModel::getTableName();

            if ($this->relationshipBuilding->isItContained($joinedModelName)) {
                if ($this->relationshipBuilding
                    ->isItContainedThrough($joinedModelName)) {
                    $tableName = $this->associativeTableName;
                    $columnName = $this->associativeModel
                        ->getAssociativeColumnNames()[$joinedModelName];
                } else {
                    $tableName = $this->tableName;
                    $columnName = $this->primaryKeyName;
                }
            } else {
                if ($this->relationshipBuilding
                    ->doesItContain($joinedModelName)) {
                    $tableName = $this->tableName;
                    $columnName = $this->relationshipBuilding
                        ->getAssociatedColumn($joinedModelName);
                } else if (isset($this->associativeModelName)) {
                    $tableName = $this->associativeTableName;
                    $columnName = $this->associativeModel
                        ->getAssociativeColumnNames()[$joinedModelName];
                } else if (isset($this->associatedModelName)) {
                    if ($this->relationshipBuilding
                        ->doesItContain($this->associatedModelName)) {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->associatedModel
                            ->getPrimaryKeyName();
                    } else {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->relationshipBuilding
                            ->getAssociatedColumn($this->associatedModelName);
                    }
                } else {
                    $tableName = $this->tableName;
                    $columnName = $this->primaryKeyName;
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
