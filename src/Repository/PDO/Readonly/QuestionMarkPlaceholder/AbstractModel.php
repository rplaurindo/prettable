<?php

namespace Repository\PDO\Readonly\QuestionMarkPlaceholder;

use
    PReTTable\QueryStatements\Component,
    PReTTable\QueryStatements\Decorators\Select,
    Repository\PDO\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {
    
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
    
    function readFrom($modelName) {
        $queryStatement = $this->resolvedRelationalSelect($modelName)->getStatement();
        
        $queryStatement .= "
        
        WHERE {$this->getTableName()}.{$this->getPrimaryKeyName()} = ?";
        
        $this->bind(1, $this->primaryKeyValue);
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStatement .= "$orderByStatement";
        }
        
        return new Component($queryStatement);
    }

}
