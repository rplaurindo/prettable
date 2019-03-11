<?php

namespace QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements\AbstractQueryComponent,
    PReTTable\QueryStatements\Select\PDO\AbstractPaginationDecorator
;

class MySQL extends AbstractPaginationDecorator {

    function __construct(AbstractQueryComponent $component, $limit, $pageNumber = 1) {
        parent::__construct($component);
        
        $this->limit = $limit;
        $this->pageNumber = $pageNumber;
    }
    
    function getStatement() {
        $offset = Pagination::calculatesOffset($this->limit, $this->pageNumber);
        
        return "
            LIMIT $this->limit
            
            OFFSET $offset";
    }

    
}
