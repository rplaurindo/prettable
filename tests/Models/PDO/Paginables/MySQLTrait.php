<?php

namespace Models\PDO\Paginables;

use
    QueryStatements\Decorators\Select\Pagination\PDO\MySQL
;

trait MySQLTrait {

    function getAll($limit = null, $pageNumber = 1) {
        $select = parent::getAll();
        $queryStatementObject = new MySQL($select, $limit, $pageNumber);

        return $queryStatementObject->getRersult();
    }

}
