<?php

namespace PreTTable\QueryStatements\WriteStrategies;

use
    PreTTable\QueryStatements
;

class Update implements QueryStatements\StrategyInterface {

    private $tableName;

    function __construct($tableName) {
        $this->tableName = $tableName;
    }

    function getStatement(array $attributes) {
        $settings = [];
        foreach ($attributes as $columnName => $value) {
            $settings[] = "$columnName = $value";
            
//             array_push($settings, "$columnName = $value");
        }

        $settingsStatement = implode(", ", $settings);

        $statement = "\n\tUPDATE $this->tableName\n\n\tSET $settingsStatement";

        return $statement;
    }

}
