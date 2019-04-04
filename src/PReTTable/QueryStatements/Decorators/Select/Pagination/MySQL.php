<?php

namespace PReTTable\QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\Select\Pagination\AbstractDecorator
;

class MySQL extends AbstractDecorator {

    function __construct(QueryStatements\AbstractComponent $component, $limit = null, $pageNumber = 1) {
        parent::__construct($component);

        $this->limit = $limit;
        $this->pageNumber = $pageNumber;
    }

    function getStatement() {
        $offset = Pagination::calculatesOffset($this->limit, $this->pageNumber);

        if (isset($this->limit)) {
            return "LIMIT $this->limit\n\n\tOFFSET $offset";
        }

        return '';
    }

}
