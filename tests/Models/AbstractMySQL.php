<?php

namespace Models;

use
    QueryStatements\Decorators\Select\Pagination
;

abstract class AbstractMySQL extends AbstractModel {
    
    function getAll($limit = null, $pageNumber = 1) {
        $select = parent::getAll();
        $queryStatementObject = new Pagination\MySQL($select, $limit, $pageNumber);
        
        return $queryStatementObject->getRersult();
    }
    
}
