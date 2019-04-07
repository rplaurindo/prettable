<?php

namespace PReTTable\Repository\PDO\Readonly\QuestionMarkPlaceholder;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements\Component,
    PReTTable\QueryStatements\Decorators\Select,
    PReTTable\Repository\PDO\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {
    
    function readFrom($modelName) {
        return $this->resolvedRelationalSelect($modelName);
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
    
    function read($columnName = null, $value = null) {
        if (!isset($columnName) || !isset($value)) {
            $columnName = $this->getPrimaryKeyName();
            $value = $this->primaryKeyValue;
        }
        
        $tableName = $this->getTableName();
        $attachTableName = false;
        $whereStatement = "WHERE $columnName = ?";
        
        if (isset($this->joinsDecorator)) {
            $joinsStatement = "\t{$this->joinsDecorator->getStatement()}";
            $attachTableName = true;
            $whereStatement = "WHERE $tableName.$columnName = ?";
        } else {
            $joinsStatement = '';
        }
        
        if (!isset($this->selectDecorator)) {
            $this->selectDecorator = new Component('SELECT ');
        }
        
        $this->selectDecorator = new Select($this->selectDecorator, $this, $attachTableName);
        
        $queryStatement = "
        {$this->selectDecorator->getStatement()}
        
        FROM $tableName";
        
        $queryStatement .= $joinsStatement;
        
        $queryStatement .= "\n\n\t$whereStatement";
        
        $this->bind(1, $value);
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }
        
        $result = $this->execute($queryStatement);
        
        if (isset($result)
            && gettype($result) == 'array'
            && count($result)
        ) {
            return $result[0];
        }
        
        return null;
    }

}
