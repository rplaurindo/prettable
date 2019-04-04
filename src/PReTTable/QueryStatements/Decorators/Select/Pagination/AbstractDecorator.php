<?php

namespace PReTTable\QueryStatements\Select\Pagination;

use
    PReTTable\QueryStatements
;

abstract class AbstractDecorator extends QueryStatements\AbstractDecorator {
    
    protected $limit;
    
    protected $pageNumber;
    
}
