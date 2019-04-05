<?php

namespace PReTTable\QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements
;

class MySQL extends AbstractDecorator {

    function __construct(QueryStatements\AbstractComponent $component, $limit = null, $pageNumber = 1) {
        parent::__construct($component);

        $this->limit = $limit;
        $this->pageNumber = $pageNumber;
        
        $this->_statement = $this->resolveStatement();
    }

    private function resolveStatement() {
        $offset = Pagination::calculatesOffset($this->limit, $this->pageNumber);

        if (isset($this->limit)) {
            return "LIMIT $this->limit\n\n\tOFFSET $offset";
        }

        return '';
    }

}
