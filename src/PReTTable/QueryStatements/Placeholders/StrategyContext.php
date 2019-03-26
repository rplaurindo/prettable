<?php

namespace PReTTable\QueryStatements\Placeholders;

class StrategyContext {

    private $strategy;

    function __construct(StrategyInterface $strategy) {
        $this->strategy = $strategy;
    }

    function getStatement(array $attributes) {
        return $this->strategy->getStatement($attributes);
    }

}
