<?php

namespace PReTTable\Repository\PDO\Readonly;

use
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
    PReTTable\Repository\PDO\AbstractModelBase
;

abstract class AbstractModel extends AbstractModelBase {

    function readAll() {
        $select = new Select($this);

        $queryStatement = "
        SELECT {$select->getStatement(...$this->getInvolvedModelNames())}

        FROM {$this->getTableName()}";

        $joinsStatement = $this->mountJoinsStatement();

        if (!empty($joinsStatement)) {
            $queryStatement .= "
            $joinsStatement";
        }

        $orderByStatement = $this->getOrderByStatement();

        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }

        $this->selectComponent = new SelectComponent($queryStatement);
        $this->selectComponent->setConnection($this->connection);

        return $this->selectComponent;
    }

}
