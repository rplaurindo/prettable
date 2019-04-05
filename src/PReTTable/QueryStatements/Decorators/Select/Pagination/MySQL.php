<?php

namespace PReTTable\QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements\AbstractComponent
;

class MySQL extends AbstractDecorator {

    function __construct(AbstractComponent $component, $limit = null, $pageNumber = 1) {
        parent::__construct($component, $limit, $pageNumber);
        
        $this->_statement = $this->resolveStatement($limit, $pageNumber);
    }

    private function resolveStatement() {
        $offset = Pagination::calculatesOffset($this->limit, $this->pageNumber);

        if (isset($this->limit)) {
            return "LIMIT $this->limit\n\n\tOFFSET $offset";
        }

        return '';
    }

}
