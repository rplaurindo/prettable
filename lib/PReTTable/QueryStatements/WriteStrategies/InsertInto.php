<?php

namespace PReTTable\QueryStatements\WriteStrategies;

use
    PReTTable\QueryStatements
;

class InsertInto implements QueryStatements\StrategyInterface {

    private $tableName;

    function __construct($tableName) {
        $this->tableName = $tableName;
    }

    function getStatement(array $attributes) {
        $insertIntoStatement =
            "$this->tableName (" . implode(", ", array_keys($attributes)) . ")";

        $valuesStatement = implode(', ', array_values($attributes));

        $statement = "
            INSERT INTO $insertIntoStatement

            VALUES ($valuesStatement)";

        return $statement;

    }

}
