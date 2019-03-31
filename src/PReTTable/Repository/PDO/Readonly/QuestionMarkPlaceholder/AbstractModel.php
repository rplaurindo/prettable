<?php

namespace PReTTable\Repository\PDO\Readonly\QuestionMarkPlaceholder;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements\Select,
    PReTTable\Repository\PDO\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {

    function read($columnName = null, $value = null) {
        $select = new Select($this);
        $selectStatement = "SELECT {$select->getStatement()}";

        if (!isset($columnName) || !isset($value)) {
            $columnName = $this->getPrimaryKeyName();
            $value = $this->primaryKeyValue;
        }

        $queryStatement = "
            $selectStatement

            FROM {$this->getTableName()}

            WHERE $columnName = ?";

        try {
            $PDOstatement = $this->connection->prepare($queryStatement);
            $PDOstatement->bindParam(1, $value);
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

}
