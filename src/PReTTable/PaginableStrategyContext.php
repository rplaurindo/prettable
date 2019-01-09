<?php

namespace PReTTable;

class PaginableStrategyContext {
    
    private $_pagerStrategy;
    
    function __construct(PaginableStrategyInterface $pagerStrategy = null) {
        $this->_pagerStrategy = $pagerStrategy;
    }
    
    function getStatement($limit, $pageNumber = 1) {
        return $this->_pagerStrategy->getStatement($limit);
    }
    
    function setStrategy(PaginableStrategyInterface $pagerStrategy) {
        $this->_pagerStrategy = $pagerStrategy;
    }
    
}
