<?php

namespace Models\PDO\Paginables;

use
    PReTTable\QueryStatements\Select\Decorators\Pagination\PDO\MySQL
;

trait MySQLTrait {

    function readAll($limit = null, $pageNumber = 1) {
        $component = parent::readAll();
        $queryStatementObject = new MySQL($component, $limit, $pageNumber);

        return $queryStatementObject->getRersult();
    }

    function readFrom($modelName, $limit = null, $pageNumber = 1) {
        $component = parent::readFrom($modelName);
        $queryStatementObject = new MySQL($component, $limit, $pageNumber);

        return $queryStatementObject->getRersult();
    }

}
