<?php

namespace PReTTable\QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements\AbstractDecorator,
    PReTTable\QueryStatements\AbstractComponent
;

class MySQL extends AbstractDecorator {

    function __construct(AbstractComponent $component, $limit = null, $pageNumber = 1) {
        parent::__construct($component);
        
        $this->_statement = $this->resolveStatement($limit, $pageNumber);
    }

    private function resolveStatement($limit = null, $pageNumber = 1) {
        $offset = Pagination::calculatesOffset($limit, $pageNumber);

        if (isset($limit)) {
            return "LIMIT $limit\n\n\tOFFSET $offset";
        }

        return '';
    }

}
