<?php

namespace QueryStatements\Decorators\Select\Pagination;

use
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\AbstractComponent
;

abstract class AbstractDecorator extends QueryStatements\AbstractDecorator {
    
    protected $limit;
    
    protected $pageNumber;

    function __construct(AbstractComponent $component, $limit = null, $pageNumber = 1) {
        parent::__construct($component);
        
        $this->limit = $limit;
        
        $this->pageNumber = $pageNumber;
    }

}
