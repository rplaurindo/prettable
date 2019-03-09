<?php

namespace PReTTable\QueryStatements\Decorators\Select;

use
    PReTTable\QueryStatements
;

class Pagination implements QueryStatements\SelectPaginationDecoratorInterface {

    function __construct() {
        
    }
    
    function getStatement($limit, $pageNumber = 1) {
        
    }
    
}
