<?php

namespace PReTTable\Repository\PDO\Readonly;

use
    PReTTable\QueryStatements\Decorators\Select,
    PReTTable\QueryStatements\Component,
    PReTTable\Repository\PDO\AbstractModelBase
;

abstract class AbstractModel extends AbstractModelBase {

    function readAll() {
        $removePrimaryKeyName = false;
        $attachTableName = false;
        
        if (isset($this->joinsDecorator)) {
            $joinsStatement = "\t{$this->joinsDecorator->getStatement()}";
            $removePrimaryKeyName = true;
            $attachTableName = true;
        } else {
            $joinsStatement = '';
        }
        
        if (!isset($this->selectDecorator)) {
            $this->selectDecorator = new Component('SELECT ');
        }
        
        $this->selectDecorator = new Select($this->selectDecorator, $this, $attachTableName, $removePrimaryKeyName);
        
        $queryStatement = "
        {$this->selectDecorator->getStatement()}
        
        FROM {$this->getTableName()}";
        
        $queryStatement .= $joinsStatement;
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }
        
        return new Component($queryStatement);
    }

}
