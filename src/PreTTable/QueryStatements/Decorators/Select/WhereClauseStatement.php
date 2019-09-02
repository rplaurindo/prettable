<?php

namespace PreTTable\QueryStatements\Decorators\Select;

use
    PreTTable\Helpers\SQL\ValueAdjuster
;

class WhereClauseStatement extends AbstractWhereClauseStatement {

    function __construct($table = null) {
        parent::__construct($table);
    }

    function like($columnName, $value) {
        $clone = $this->getClone();

        $columnStatement = $columnName;

        if (isset($clone->tableName)) {
            $columnStatement = "$clone->tableName.$columnName";
        }

        $value = ValueAdjuster::adjust([$value])[0];

        $statement = "($columnStatement LIKE $value)";
        $clone->addStatement($statement);

        return $clone;
    }

    function between($columnName, $start, $end) {
        $clone = $this->getClone();

        $columnStatement = $columnName;

        if (isset($clone->tableName)) {
            $columnStatement = "$clone->tableName.$columnName";
        }

        $statement = "$columnStatement BETWEEN $start AND $end";
        $clone->addStatement($statement);

        return $clone;
    }

    protected function addStatementTo($columnName, $value) {
        $columnStatement = $columnName;

        if (isset($this->tableName)) {
            $columnStatement = "$this->tableName.$columnName";
        }

        if (gettype($value) == 'array') {
            if (count($value)) {
                $value = ValueAdjuster::adjust($value);
                $valuesStatement = implode(', ', $value);
                $statement = "($columnStatement IN ($valuesStatement))";
            }
        } else {
            $value = ValueAdjuster::adjust([$value])[0];
            $statement = "($columnStatement $this->comparisonOperator $value)";
        }

        if (isset($statement)) {
            $this->addStatement($statement);
        }

        return $this;
    }

}
