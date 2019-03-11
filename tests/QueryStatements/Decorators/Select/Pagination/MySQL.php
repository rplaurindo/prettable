<?php

namespace QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements\AbstractSelectComponent,
    PReTTable\QueryStatements\PDO\AbstractSelectPaginationDecorator
;

class MySQL extends AbstractSelectPaginationDecorator {

    function __construct(AbstractSelectComponent $component, $limit, $pageNumber = 1) {
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
