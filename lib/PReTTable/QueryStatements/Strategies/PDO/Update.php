<?php

namespace PReTTable\QueryStatements\Strategies\PDO;

use
    PReTTable\QueryStatementStrategyInterface
;

class Update implements QueryStatementStrategyInterface {

    private $tableName;

    private $primaryKeyName;

    function __construct($tableName, $primaryKeyName) {
        $this->tableName = $tableName;
        $this->primaryKeyName = $primaryKeyName;
    }

    function getStatement(array $attributes) {
        $whereStatement = "$this->primaryKeyName = :$this->primaryKeyName";

        $settings = [];
        foreach (array_keys($attributes) as $columnName) {
            array_push($settings, "$columnName = :$columnName");
        }

        $settingsStatement = implode(', ', $settings);

        $statement = "
            UPDATE $this->tableName
            SET $settingsStatement
            WHERE $whereStatement
        ";

        return $statement;
    }

}
