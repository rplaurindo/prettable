<?php

namespace PReTTable\DAO;

use
    PReTTable\AbstractModel
;

abstract class AbstractModelBase extends AbstractModel {

    protected function getJoinsStatement() {
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
