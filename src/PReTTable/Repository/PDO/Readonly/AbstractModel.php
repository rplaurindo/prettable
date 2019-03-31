<?php

namespace PReTTable\Repository\PDO\Readonly;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
    PReTTable\Repository\PDO\AbstractModelBase
;

abstract class AbstractModel extends AbstractModelBase {

    function readAll() {
        $select = new Select($this->name);

        $queryStatement = "
            SELECT {$select->getStatement(...$this->getInvolvedModelNames())}

            FROM {$this->getTableName()}";

        $joinsStatement = $this->mountJoinsStatement();

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
//         $relationalSelectBuilding = $this->relationalSelectBuilding->build($modelName, $this->primaryKeyValue);
        $relationalSelectBuilding = $this->build($modelName, $this->primaryKeyValue);

//         $select = $relationalSelectBuilding->getSelect();
//         // o from é a tabela do modelName salvo quando isItContainedThrough
//         $from = $relationalSelectBuilding->getFrom();
//         $whereClause = $relationalSelectBuilding->getWhereClause();

//         $joinsStatement = "";

//         $queryStatement = "
//             SELECT $select

//             FROM $from";

//         $joins = $relationalSelectBuilding->getJoins();
//         if (count($joins)) {
//             $joinsStatement .= "
//             INNER JOIN " .
//             implode("
//             INNER JOIN ", $joins);
//         }

//         if (!empty($joinsStatement)) {
//             $queryStatement .= "
//                 $joinsStatement";
//         }

//         $queryStatement .= "
//             WHERE $whereClause";

//         $orderByStatement = $this->getOrderBy();

//         if (isset($orderByStatement)) {
//             $queryStatement .= "
//                 $orderByStatement";
//         }
        
//         echo "$queryStatement\n\n";

//         $component = new SelectComponent($queryStatement);
//         $component->setConnection($this->connection);

//         return $component;
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
