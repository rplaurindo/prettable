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
        
        $sql = "\n\t{$this->selectDecorator->getStatement()}\n\n\tFROM $tableName";
        
        $sql .= $joinsStatement;
        
        $sql .= "\n\n\t$whereStatement";
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $sql .= $orderByStatement;
        }
        
        $this->setBindings([$value]);
        
        $result = $this->execute($sql);
        
        $allFetched = $result->fetchAll();
        
        if (gettype($allFetched) === 'array'
            && count($allFetched)
        ) {
            return $allFetched[0];
        }
        
        return null;
    }
    
    function readFromComponent($modelName) {
        $sql = $this->resolvedRelationalSelect($modelName)->getStatement();
        
        $sql .= "\n\n\tWHERE {$this->getTableName()}.{$this->getPrimaryKeyName()} = ?";
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $sql .= "$orderByStatement";
        }
        
        return new Component($sql);
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
