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

        $orderByStatement = $this->getOrderByStatement();

        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }

        $this->queryComponent = new SelectComponent($queryStatement);

        return $this->queryComponent;
    }

}
