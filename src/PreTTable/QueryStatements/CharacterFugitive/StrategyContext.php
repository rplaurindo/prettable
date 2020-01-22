<?php

namespace PreTTable\QueryStatements\CharacterFugitive;

class StrategyContext {

    private $strategy;

    function __construct(StrategyInterface $strategy) {
        $this->strategy = $strategy;
    }

    function getEscaped(array $values) {
        return $this->strategy->getEscaped($values);
    }

}
