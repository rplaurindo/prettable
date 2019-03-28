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

    function join($tableName, $columnName, $leftTableColumnName, $type = 'INNER') {
        $clone = $this->getClone();

        $joinedColumns = [
            'columnName' => $columnName,
            'leftTableColumnName' => $leftTableColumnName
        ];

        if ($clone->joins->offsetExists($type)) {
            $join = $clone->joins->offsetGet($type);

            if (!array_key_exists($tableName, $join)) {
                $join[$tableName] = $joinedColumns;
            }
        } else {
            $join = [];
            $join[$tableName] = $joinedColumns;
        }

        $clone->joins->offsetSet($type, $join);

        return clone $clone;
    }

    function getJoinsStatement() {
        $statement = '';

        foreach ($this->joins as $type => $join) {
            $joinedTables = array_keys($join);

            foreach ($joinedTables as $joinedTableName) {
                $joinedColumns = $join[$joinedTableName];

                $columnName = $joinedColumns['columnName'];
                $leftTableColumnName = $joinedColumns['leftTableColumnName'];

                $leftTableName = $this->getTableName();

                $statement .= "$type JOIN $joinedTableName ON $joinedTableName.$columnName = $leftTableName.$leftTableColumnName\n";
            }

        }

        return $statement;
    }

}
