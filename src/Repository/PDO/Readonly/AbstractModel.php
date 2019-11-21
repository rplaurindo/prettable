<?php

namespace Repository\PDO\Readonly;

use
    PreTTable\QueryStatements\Decorators\Select
    , PreTTable\QueryStatements\Component
    , Repository\PDO\AbstractModelBase
;

abstract class AbstractModel extends AbstractModelBase {
    
    function countComponent() {
        if (isset($this->joinsDecorator)) {
            $joinsStatement = "\t{$this->joinsDecorator->getStatement()}";
        } else {
            $joinsStatement = '';
        }
        
        $this->selectDecorator = new Component("SELECT\n\tcount(*)");
        
        $queryStringStatement = "\n{$this->selectDecorator->getStatement()}\n\n\tFROM {$this->getTableName()}";
        
        $queryStringStatement .= $joinsStatement;
        
        return new Component($queryStringStatement);
    }
    
    function count() {
        $component = $this->countComponent();
        $sqlStatement = $component->getStatement();
        
        return $this->execute($sqlStatement)->fetchColumn();
    }

    function readAllComponent() {
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
        {$this->selectDecorator->getStatement()}\n\n\tFROM {$this->getTableName()}";
        
        $queryStringStatement .= $joinsStatement;
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStringStatement .= $orderByStatement;
        }
        
        return new Component($queryStringStatement);
    }

}
