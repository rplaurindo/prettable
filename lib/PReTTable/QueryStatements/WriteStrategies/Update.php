<?php

namespace PReTTable\QueryStatements\WriteStrategies;

use
    PReTTable\QueryStatements
;

class Update implements QueryStatements\StrategyInterface {

    private $tableName;

    private $primaryKeyName;

    private $primaryKeyValue;

    function __construct($tableName, $primaryKeyName, $primaryKeyValue) {
        $this->tableName = $tableName;
        $this->primaryKeyName = $primaryKeyName;
        $this->primaryKeyValue = $primaryKeyValue;
    }

    function getStatement(array $attributes) {
        $settings = [];
        foreach ($attributes as $columnName => $value) {
            array_push($settings, "$columnName = $value");
        }

        $settingsStatement = implode(', ', $settings);

        $whereStatement = "$this->primaryKeyName = $this->primaryKeyValue";

        $statement = "
            UPDATE $this->tableName

            SET $settingsStatement

            WHERE $whereStatement";

        return $statement;
    }

}
