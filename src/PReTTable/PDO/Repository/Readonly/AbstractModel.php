<?php

namespace PReTTable\PDO\Repository\Readonly;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
    PReTTable\PDO\Repository
;

abstract class AbstractModel extends Repository\AbstractModelBase {

    function read($columnName = null, $value = null) {
        $select = new Select\Repository($this);
        $selectStatement = "SELECT {$select->getStatement()}";

        if (!isset($columnName) || !isset($value)) {
            $columnName = $this->getPrimaryKeyName();
            $value = $this->primaryKeyValue;
        }

        $queryStatement = "
            $selectStatement

            FROM $this->tableName

            WHERE $columnName = :$columnName";

        try {
            $PDOstatement = $this->connection->prepare($queryStatement);
            $PDOstatement->bindParam(":$columnName", $value);
            $PDOstatement->execute();

            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }

        if (
            isset($result) &&
            gettype($result) == 'array' &&
            count($result)
            ) {
            return $result[0];
        }

        return null;
    }

    function readAll() {
        $select = new Select\Repository($this->name);

        $queryStatement = "
            SELECT {$select->getStatement(...$this->relationalSelectBuilding->getInvolvedModelNames())}

            FROM $this->tableName";

        $joinsStatement = "";

        $joins = $this->relationalSelectBuilding->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }

        if (!empty($joinsStatement)) {
            $queryStatement .= "
                $joinsStatement";
        }

        $orderByStatement = $this->getOrderBy();

        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }

        $component = new SelectComponent($queryStatement);
        $component->setConnection($this->connection);

        return $component;
    }

    function readFrom($modelName) {
        $relationalSelectBuilding = $this->relationalSelectBuilding->build($modelName, $this->primaryKeyValue);

        $select = $relationalSelectBuilding->getSelect();
        $from = $relationalSelectBuilding->getFrom();
        $whereClause = $relationalSelectBuilding->getWhereClause();

        $joinsStatement = "";

        $queryStatement = "
            SELECT $select

            FROM $from";

        $joins = $relationalSelectBuilding->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }

        if (!empty($joinsStatement)) {
            $queryStatement .= "
                $joinsStatement";
        }

        $queryStatement .= "
            WHERE $whereClause";

        $orderByStatement = $this->getOrderBy();

        if (isset($orderByStatement)) {
            $queryStatement .= "
                $orderByStatement";
        }

        $component = new SelectComponent($queryStatement);
        $component->setConnection($this->connection);

        return $component;
    }

    function readParent($modelName) {
        $relationalSelectBuilding = $this->relationalSelectBuilding->build($modelName, $this->primaryKeyValue);

        $select = $relationalSelectBuilding->getSelect();
        $from = $relationalSelectBuilding->getFrom();
        $whereClause = $relationalSelectBuilding->getWhereClause();

        $joinsStatement = "";

        $queryStatement = "
            SELECT $select

            FROM $from";

        $joins = $relationalSelectBuilding->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }

        if (!empty($joinsStatement)) {
            $queryStatement .= "
                $joinsStatement";
        }

        $queryStatement .= "
            WHERE $whereClause";

        echo "$queryStatement\n\n";

        try {
            $PDOstatement = $this->connection->query($queryStatement);

            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }

        if (
            isset($result) &&
            gettype($result) == 'array' &&
            count($result)
            ) {
            return $result[0];
        }

        return null;
    }

}
