<?php

namespace PReTTable\Repository;

use
    Exception,
    ArrayObject,
    PReTTable\Reflection,
    PReTTable\QueryStatements\Select
;

// a layer to mount a map of queries to select data
class RelationalSelectBuilding {

    private $relationshipBuilding;

    private $modelName;

    private $tableName;

    private $primaryKeyName;

    private $associatedModelName;

    private $associatedModel;

    private $associatedTableName;

    private $associativeModelName;

    private $associativeTableName;

    private $associativeModel;

    private $involvedModelNames;

    private $involvedTableNames;

    private $select;

    private $selectStatement;

    private $fromStatement;

    private $joins;

    private $whereClauseStatement;

    private $order;

    private $by;

    function __construct(RelationshipBuilding $relationshipBuilding) {
        $this->relationshipBuilding = $relationshipBuilding;

        $this->modelName = $relationshipBuilding->getModelName();
        $this->tableName = $relationshipBuilding->getTableName();
        $this->primaryKeyName = $relationshipBuilding->getPrimaryKeyName();

        $this->joins = new ArrayObject();

        $this->involvedModelNames = new ArrayObject();
        $this->involvedTableNames = new ArrayObject();

//         $this->select = new Select($this->modelName);
    }

    function join($modelName, $associatedColumn) {
        RelationshipBuilding::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');

        $clone = $this->getClone();

        if (!$clone->joins->offsetExists($modelName)) {
            $clone->joins->offsetSet($modelName, $associatedColumn);
        }

        return clone $clone;
    }

    function addsInvolved($modelName) {
        $this->involvedModelNames->append($modelName);
        $this->involvedTableNames->append(RelationshipBuilding::resolveTableName($modelName));
    }

    function getInvolvedModelNames() {
        return $this->involvedModelNames->getArrayCopy();
    }

    function build($modelName) {
        RelationshipBuilding::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');

        $clone = $this->getClone();

        if ($clone->relationshipBuilding->isItContained($modelName)
            || $clone->relationshipBuilding->doesItContain($modelName)) {

            $primaryKeyValue = $clone->relationshipBuilding
                ->getPrimaryKeyValue();
            $clone->associatedModelName = $modelName;
            $clone->associatedModel = Reflection::getDeclarationOf($modelName);
            $clone->associatedTableName = RelationshipBuilding
                ::resolveTableName($modelName);

            $clone->select = new Select($clone->associatedModelName);
            $clone->fromStatement = $clone->associatedTableName;

            if ($clone->relationshipBuilding->isItContained($modelName)) {

                if ($clone->relationshipBuilding
                    ->isItContainedThrough($modelName)) {

                    $clone->associativeModelName = $clone
                        ->relationshipBuilding
                        ->getAssociativeModelNameOf($modelName);

                    $clone->addsInvolved($clone->associativeModelName);

                    $clone->associativeModel = Reflection
                        ::getDeclarationOf($clone->associativeModelName);

                    $clone->associativeTableName = RelationshipBuilding
                        ::resolveTableName($clone->associativeModelName);
                    $clone->fromStatement = $clone->associativeTableName;

                    $clone->join($clone->modelName, $clone->primaryKeyName);
                    $clone->join($modelName, $clone->associatedModel
                        ->getPrimaryKeyName());

                    $associativeColumn = $clone->associativeModel
                        ->getAssociativeKeys()[$clone->modelName];
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
                ->getStatement(true, ...$clone->getInvolvedModelNames());
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
            $joinedTableName = RelationshipBuilding::resolveTableName($joinedModelName);

            if ($this->relationshipBuilding->isItContained($joinedModelName)) {
                if ($this->relationshipBuilding
                    ->isItContainedThrough($joinedModelName)) {
                    $tableName = $this->associativeTableName;
                    $columnName = $this->associativeModel
                        ->getAssociativeKeys()[$joinedModelName];
                } else {
                    $tableName = $this->tableName;
                    $columnName = $this->primaryKeyName;
                }
            } else {
                if ($this->relationshipBuilding->doesItContain($joinedModelName)) {
                    $tableName = $this->tableName;
                    $columnName = $this->relationshipBuilding->getAssociatedColumn($joinedModelName);
                } else if (isset($this->associativeModelName)) {
                    $tableName = $this->associativeTableName;
                    $columnName = $this->associativeModel
                        ->getAssociativeKeys()[$joinedModelName];
                } else if (isset($this->associatedModelName)) {
                    if ($this->relationshipBuilding->doesItContain($this->associatedModelName)) {
                        $tableName = $this->associatedTableName;
                        $columnName = $this->associatedModel->getPrimaryKeyName();
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

    function setOrder($columnName, $by = '') {
        $this->order = $columnName;
        $this->by = $by;
    }

    function resolveOrderBy() {
        if (count($this->getInvolvedTableNames())) {
            $explodedOrder = explode('.', $this->order);

            if (count($explodedOrder) != 2
                || !in_array($explodedOrder[0], $this->getInvolvedTableNames())) {
                throw new Exception("The defined column of \"ORDER BY\" statement must be fully qualified containing " . implode(' or ', $this->getInvolvedTableNames()));
            }
        }

        return "
            ORDER BY $this->order $this->by";
    }

    protected function getClone() {
        return clone $this;
    }

    private function getInvolvedTableNames() {
        return $this->involvedTableNames->getArrayCopy();
    }

}
