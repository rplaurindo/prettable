<?php

namespace PreTTable\QueryStatements\Placeholders;

interface StrategyInterface {

    function getStatement(array $attributes);

}
