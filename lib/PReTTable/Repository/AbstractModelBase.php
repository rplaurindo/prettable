<?php

namespace PReTTable\Repository;

use
    PReTTable,
    Exception
;

abstract class AbstractModelBase extends PReTTable\AbstractModel {

    protected $relationshipBuilding;

    protected $relationalSelectBuilding;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->relationshipBuilding = new RelationshipBuilding($this->getName());
        $this->relationalSelectBuilding = new RelationalSelectBuilding($this->relationshipBuilding, $this);
    }

    function join($modelName, $associatedColumn) {
        $clone = $this->getClone();

        $clone->relationalSelectBuilding->join($modelName, $associatedColumn);

        $clone->addsInvolvedModel($modelName);

        return $clone;
    }

    protected function contains($modelName, $associatedColumn) {
        $this->relationshipBuilding->contains($modelName, $associatedColumn);
    }

    protected function isContained($modelName, $associatedColumn) {
        $this->relationshipBuilding->isContained($modelName, $associatedColumn);
    }

    protected function containsThrough($modelName, $through) {
        $this->relationshipBuilding->containsThrough($modelName, $through);
    }

    protected function getOrderBy() {
        if (isset($this->orderBy)) {

            if (count($this->getInvolvedTableNames())) {
                $explodedOrderByStatement = explode('.', $this->orderBy);

                if (count($explodedOrderByStatement) != 2
                    || !in_array($explodedOrderByStatement[0], $this->getInvolvedTableNames())
                    ) {
                        throw new Exception("The defined column of \"ORDER BY\" statement must be fully qualified containing " . implode(' or ', $this->getInvolvedTableNames()));
                    }
            }

            return "
            ORDER BY $this->orderBy $this->orderOfOrderBy";
        }

        return null;
    }

}
