<?php

namespace PReTTable\QueryStatements\WriteStrategies;

use
    PReTTable\QueryStatements
;

class Update implements QueryStatements\StrategyInterface {

    private $tableName;

    function __construct($tableName) {
        $this->tableName = $tableName;
    }

    function getStatement(array $attributes) {
        $settings = [];
        foreach ($attributes as $columnName => $value) {
            array_push($settings, "$columnName = $value");
        }

        $settingsStatement = implode(', ', $settings);

        $statement = "
        UPDATE $this->tableName

        SET $settingsStatement";

        return $statement;
    }

}
