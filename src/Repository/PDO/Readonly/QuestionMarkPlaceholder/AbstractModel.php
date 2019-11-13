<?php

namespace Repository\PDO\Readonly\QuestionMarkPlaceholder;

use
    PreTTable\QueryStatements\Component,
    PreTTable\QueryStatements\Decorators\Select,
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
        
        $queryStringStatement = "
        {$this->selectDecorator->getStatement()}
        
        FROM $tableName";
        
        $queryStringStatement .= $joinsStatement;
        
        $queryStringStatement .= "\n\n\t$whereStatement";
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStringStatement .= $orderByStatement;
        }
        
        $result = $this->execute($queryStringStatement, [$value]);
        
        if (isset($result)
            && gettype($result) == 'array'
            && count($result)
        ) {
            return $result[0];
        }
        
        return null;
    }
    
    function readFrom($modelName) {
        $queryStringStatement = $this->resolvedRelationalSelect($modelName)->getStatement();
        
        $queryStringStatement .= "
        
        WHERE {$this->getTableName()}.{$this->getPrimaryKeyName()} = ?";
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStringStatement .= "$orderByStatement";
        }
        
        return new Component($queryStringStatement);
    }
    
    function readParent($modelName) {
        $result = $this->readFrom($modelName);
        
        if (isset($result)
            && gettype($result) == 'array'
            &&count($result)
            ) {
            return $result[0];
        }
        
        return null;
    }

}
