<?php

namespace PReTTable\Repository\PDO\Readonly\QuestionMarkPlaceholder;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
    PReTTable\Repository\PDO\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {

    function read($columnName = null, $value = null) {
        $select = new Select($this);

        if (!isset($columnName) || !isset($value)) {
            $columnName = $this->getPrimaryKeyName();
            $value = $this->primaryKeyValue;
        }

        $queryStatement = "
        SELECT {$select->getStatement()}

        FROM {$this->getTableName()}

        WHERE $columnName = ?";
        
        echo "$queryStatement\n\n";

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
    
    function readFrom($modelName) {
        $query = $this->build($modelName);

        $joinsStatement = "";

        $queryStatement = "
        SELECT {$query->getSelectStatement()}

        FROM {$query->getFromStatement()}{$this->mountJoinsStatement()}";

        $orderByStatement = $this->getOrderByStatement();

        if (isset($orderByStatement)) {
            $queryStatement .= "$orderByStatement";
        }

        $component = new SelectComponent($queryStatement);
        $component->setConnection($this->connection);

        return $component;
    }
    
    function readParent($modelName) {
        $query = $this->build($modelName);
        
        $queryStatement = "
        SELECT {$query->getSelectStatement()}
        
        FROM {$query->getFromStatement()}{$this->mountJoinsStatement()}
        
        WHERE {$this->getTableName()}.{$this->getPrimaryKeyName()} = ?";
        
        echo "$queryStatement\n\n";
        
        try {
            $PDOstatement = $this->connection->prepare($queryStatement);
            $PDOstatement->bindParam(1, $this->primaryKeyValue);
            $PDOstatement->execute();
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        if (isset($result)
            && gettype($result) == 'array'
            && count($result)
            ) {
            return $result[0];
        }
        
        return null;
    }

}
