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
        
        $sql = "\n{$this->selectDecorator->getStatement()}\n\n\tFROM {$this->getTableName()}";
        
        $sql .= $joinsStatement;
        
        return new Component($sql);
    }
    
    function count() {
        $component = $this->countComponent();
        $sql = $component->getStatement();
        
        return $this->execute($sql)->fetchColumn();
    }

    function readAllComponent() {
        $attachTableName = false;
        
        if (isset($this->joinsDecorator)) {
            $joinsStatement = "\t{$this->joinsDecorator->getStatement()}";
            $attachTableName = true;
        } else {
            $joinsStatement = '';
        }
        
        $component = new Component('SELECT ');
//         if (!isset($this->selectDecorator)) {
//             $component = new Component('SELECT ');
//         } else {
//             $component = $this->selectDecorator;
//         }
        
        $this->selectDecorator = new Select($component, $this, $attachTableName);
        
        $sql = "\n\t{$this->selectDecorator->getStatement()}\n\n\tFROM {$this->getTableName()}";
        
        $sql .= $joinsStatement;
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $sql .= $orderByStatement;
        }
        
        return new Component($sql);
    }

}
