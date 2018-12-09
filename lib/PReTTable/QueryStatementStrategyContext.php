<?php

namespace PReTTable;

class QueryStatementStrategyContext {
    
    private $strategy;

    function __construct(QueryStatementStrategyInterface $strategy) {
        $this->strategy = $strategy;
    }
    
    function getStatement(array $attributes) {
        return $this->strategy->getStatement($attributes);
    }
    
}
