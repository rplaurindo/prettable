<?php

namespace PReTTable\DAO;

use
    ArrayObject,
    PReTTable
;

abstract class AbstractModelBase extends PReTTable\AbstractModel {

    private $joins;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->joins = new ArrayObject();
    }

    function join($modelName, $joinedModelNameColumn, $leftModelNameColumn, $type = 'INNER') {
        $clone = $this->getClone();

        $joinColumns = [
            'joinedModelNameColumn' => $joinedModelNameColumn,
            'leftModelNameColumn' => $leftModelNameColumn
        ];

        if ($clone->joins->offsetExists($type)) {
            $join = $clone->joins->offsetGet($type);

            if (!array_key_exists($modelName, $join)) {
                $join[$modelName] = $joinColumns;
            }
        } else {
            $join = [];
            $join[$modelName] = $joinColumns;
        }

        $clone->joins->offsetSet($type, $join);

        return clone $clone;
    }

    function getJoinsStatement() {

    }

}
