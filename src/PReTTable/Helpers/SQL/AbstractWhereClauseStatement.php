<?php

namespace PReTTable\Helpers\SQL;

abstract class AbstractWhereClauseStatement {

    protected $tableName;

    protected $comparisonOperator;

    protected $logicalOperator;

    protected $statement;

    function __construct($tableName = null) {
        $this->tableName = $tableName;
        $this->comparisonOperator = '=';
        $this->logicalOperator = 'AND';
        $this->statement = '';
    }

    function setComparisonOperator($operator) {
        $this->comparisonOperator = $operator;
    }

    function setLogicalOperator($operator) {
        $this->logicalOperator = $operator;
    }

    function addStatements(array $params) {
        $clone = $this->getClone();

        foreach($params as $columnName => $value) {
            $clone->addStatementTo($columnName, $value);
        }

        return $clone;
    }

    function addStatement($statement) {
        if (empty($this->statement)) {
            $this->statement .= $statement;
        } else {
            $this->statement .= "
                $this->logicalOperator $statement
            ";
        }
    }

    function addOr(AbstractWhereClauseStatement $statement) {
        $clone = $this->getClone();

        $clone->statement .= " OR ({$statement->getStatement()})";

        return $clone;
    }

    function addAnd(AbstractWhereClauseStatement $statement) {
        $clone = $this->getClone();

        $clone->statement .= " AND ({$statement->getStatement()})";

        return $clone;
    }

    function getStatement() {
        return $this->statement;
    }

    abstract function like($columnName, $value);

    abstract function between($columnName, $start, $end);

    protected abstract function addStatementTo($columnName, $value);

    protected function getClone() {
        return clone $this;
    }
}
