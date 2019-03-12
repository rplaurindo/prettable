<?php

namespace Models\Paginables;

use
    QueryStatements\Decorators\Select\Pagination
;

trait MySQLTrait {

    function getAll($limit = null, $pageNumber = 1) {
        $select = parent::getAll();
        $queryStatementObject = new Pagination\MySQL($select, $limit, $pageNumber);

        return $queryStatementObject->getRersult();
    }

}
