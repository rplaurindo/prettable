<?php

namespace PReTTable\QueryStatements\Placeholders;

interface StrategyInterface {

    function getStatement(array $attributes);

}
