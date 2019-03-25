<?php

namespace PReTTable\QueryStatements\WriteStrategies\DAO;

use
    PReTTable\QueryStatements,
    PReTTable\Helpers\SQL
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
        $whereStatement = "$this->primaryKeyName = $this->primaryKeyValue";
        $values = SQL\ValueAdjuster::adjust(...array_values($attributes));

        $settings = [];
        foreach (array_keys($attributes) as $index => $columnName) {
            $value = $values[$index];
            array_push($settings, "$columnName = $value");
        }

        $settingsStatement = implode(', ', $settings);

        $statement = "
            UPDATE $this->tableName

            SET $settingsStatement

            WHERE $whereStatement";

        return $statement;
    }

}
