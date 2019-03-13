<?php

namespace PReTTable\PDO\Repository\Readonly;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
    PReTTable\PDO\Repository
;

abstract class AbstractModel extends Repository\AbstractModel {

    function read() {
        $clone = $this->getClone();

        $select = new Select($clone->name);
        $selectStatement = "SELECT {$select->getStatement()}";

        $primaryKeyName = $clone->getPrimaryKeyName();

        $queryStatement = "
            $selectStatement

            FROM $clone->tableName
            WHERE $primaryKeyName = :$primaryKeyName";

        echo "$queryStatement\n\n";

        try {
            $PDOstatement = $clone->connection->prepare($queryStatement);
            $PDOstatement->bindParam(":$primaryKeyName", $clone->primaryKeyValue);
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

    function getAll() {
        $select = new Select($this->name);

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

    function getModel($modelName) {
        $clone = $this->getClone();

        $relationalSelectBuilding = $clone->relationalSelectBuilding->build($modelName, $clone->primaryKeyValue);

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

        $orderByStatement = $clone->getOrderBy();

        if (isset($orderByStatement)) {
            $queryStatement .= "
                $orderByStatement";
        }

        $component = new SelectComponent($queryStatement);
        $component->setConnection($this->connection);

        return $component;
    }

    function getParent($modelName) {
        $clone = $this->getClone();

        $relationalSelectBuilding = $clone->relationalSelectBuilding->build($modelName, $clone->primaryKeyValue);

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
            $PDOstatement = $clone->connection->query($queryStatement);

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
