<?php

namespace Repository\PDO\Readonly;

use
    PreTTable\QueryStatements\Decorators\Select,
    PreTTable\QueryStatements\Component,
    Repository\PDO\AbstractModelBase
;

abstract class AbstractModel extends AbstractModelBase {

    function readAll() {
        $attachTableName = false;
        
        if (isset($this->joinsDecorator)) {
            $joinsStatement = "\t{$this->joinsDecorator->getStatement()}";
            $attachTableName = true;
        } else {
            $joinsStatement = '';
        }
        
        if (!isset($this->selectDecorator)) {
            $this->selectDecorator = new Component('SELECT ');
        }
        
        $this->selectDecorator = new Select($this->selectDecorator, $this, $attachTableName);
        
        $queryStringStatement = "
        {$this->selectDecorator->getStatement()}
        
        FROM {$this->getTableName()}";
        
        if (isset($this->whereDecorator)) {
            $queryStringStatement .= "\t{$this->whereDecorator->getStatement()}";
        }
        
        $queryStringStatement .= $joinsStatement;
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStringStatement .= $orderByStatement;
        }
        
        return new Component($queryStringStatement);
    }

}
