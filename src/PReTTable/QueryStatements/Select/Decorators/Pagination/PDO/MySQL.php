<?php

namespace PReTTable\QueryStatements\Select\Decorators\Pagination\PDO;

use
    PReTTable\Helpers\Pagination,
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\Select\PDO
;

class MySQL extends PDO\AbstractPaginationDecorator {

    function __construct(QueryStatements\AbstractComponent $component, $limit = null, $pageNumber = 1) {
        parent::__construct($component);

        $this->limit = $limit;
        $this->pageNumber = $pageNumber;
    }

    function getStatement() {
        $offset = Pagination::calculatesOffset($this->limit, $this->pageNumber);

        if (isset($this->limit)) {
            return "\tLIMIT $this->limit\n\n\tOFFSET $offset";
        }

        return '';
    }

}
