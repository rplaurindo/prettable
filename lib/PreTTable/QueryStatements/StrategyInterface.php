<?php

namespace PreTTable\QueryStatements;

interface StrategyInterface {

    function getStatement(array $attributes);
    
}
