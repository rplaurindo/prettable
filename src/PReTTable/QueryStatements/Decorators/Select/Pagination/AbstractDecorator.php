<?php

namespace PReTTable\QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\QueryStatements
;

abstract class AbstractDecorator extends QueryStatements\AbstractDecorator {
    
    protected $limit;
    
    protected $pageNumber;
    
}
