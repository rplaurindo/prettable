<?php

namespace PReTTable\QueryStatements\WriteStrategies\Repository\NamedPlaceholder;

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

        $values = [];
        foreach (array_keys($attributes) as $columnName) {
            array_push($values, ":$columnName");
        }

        $valuesStatement = implode(', ', $values);

        $statement = "
            INSERT INTO $insertIntoStatement
            VALUES ($valuesStatement)
        ";

        return $statement;

    }

}
