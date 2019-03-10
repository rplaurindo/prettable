<?php

namespace QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements\AbstractSelectComponent,
    PReTTable\QueryStatements\PDO\AbstractSelectPaginationDecorator
;

class MySQL extends AbstractSelectPaginationDecorator {

    function __construct(AbstractSelectComponent $component) {
        parent::__construct($component);
    }
    
    function getStatement($limit, $pageNumber = 1) {
        $offset = Pagination::calculatesOffset($limit, $pageNumber);
        
        return "
            LIMIT $limit
            OFFSET $offset";
    }
    
}
