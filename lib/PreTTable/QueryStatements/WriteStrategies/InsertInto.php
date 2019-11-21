<?php

namespace PreTTable\QueryStatements\WriteStrategies;

use
    PreTTable\QueryStatements
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

        $statement = "\n\tINSERT INTO $insertIntoStatement\n\n\tVALUES ($valuesStatement)";

        return $statement;

    }

}
