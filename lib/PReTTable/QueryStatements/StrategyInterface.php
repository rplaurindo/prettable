<?php

namespace PReTTable\QueryStatements;

interface StrategyInterface {

    function getStatement(array $attributes);
    
}
