<?php

namespace QueryStatements\Placeholders\Strategies;

use
    PReTTable\QueryStatements\Placeholders\StrategyInterface
;

class QuestionMark implements StrategyInterface {

    function getStatement(array $attributes) {
        foreach (array_keys($attributes) as $columnName) {
            $attributes[$columnName] = '?';
        }

        return $attributes;
    }

}
