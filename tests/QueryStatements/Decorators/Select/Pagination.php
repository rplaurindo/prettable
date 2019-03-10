<?php

namespace QueryStatements\Decorators\Select;

use
    PReTTable\QueryStatements\AbstractSelectPaginationDecorator,
    PReTTable\QueryStatements\SelectComponentInterface
;

class Pagination extends AbstractSelectPaginationDecorator {

    function __construct(SelectComponentInterface $component) {
        parent::__construct($component);
    }
    
    function getStatement($limit, $pageNumber = 1) {
        
    }
    
}
