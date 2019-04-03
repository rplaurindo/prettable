<?php

namespace Models\PDO\Paginables;

use
    PReTTable\QueryStatements\Select\Decorators\PDO\Pagination\MySQL
;

trait MySQLTrait {

    function readAll($limit = null, $pageNumber = 1) {
        $component = parent::readAll();

        return new MySQL($component, $limit, $pageNumber);
    }

    function readFrom($modelName, $limit = null, $pageNumber = 1) {
        $component = parent::readFrom($modelName);
        $queryStatementObject = new MySQL($component, $limit, $pageNumber);

        return $queryStatementObject->getResult();
    }

}
