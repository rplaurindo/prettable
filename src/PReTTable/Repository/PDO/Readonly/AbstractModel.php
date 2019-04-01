<?php

namespace PReTTable\Repository\PDO\Readonly;

use
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
    PReTTable\Repository\PDO\AbstractModelBase
;

abstract class AbstractModel extends AbstractModelBase {

    function readAll() {
        $select = new Select($this->name);

        $queryStatement = "
        SELECT {$select->getStatement(...$this->getInvolvedModelNames())}

        FROM {$this->getTableName()}";

        $joinsStatement = $this->mountJoinsStatement();

        if (!empty($joinsStatement)) {
            $queryStatement .= "
            $joinsStatement";
        }

        $orderByStatement = $this->getOrderBy();

        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }

        $component = new SelectComponent($queryStatement);
        $component->setConnection($this->connection);

        return $component;
    }

}
