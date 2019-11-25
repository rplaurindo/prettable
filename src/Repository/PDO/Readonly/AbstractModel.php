<?php

namespace Repository\PDO\Readonly;

use
    PreTTable\QueryStatements\Decorators\ColumnSelect
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
        
        $this->columnSelectDecorator = new Component("SELECT\n\tcount(*)");
        
        $sql = "\n{$this->columnSelectDecorator->getStatement()}\n\tFROM {$this->getTableName()}";
        
        $sql .= $joinsStatement;
        
        return new Component($sql);
    }
    
    function count() {
        $component = $this->countComponent();
        $sql = $component->getStatement();
        
        $result = $this->execute($sql);
        
        return $result->fetchColumn();
    }

    function readAllComponent() {
        $attachTableName = false;
        
        if (isset($this->joinsDecorator)) {
            $joinsStatement = "\t{$this->joinsDecorator->getStatement()}";
            $attachTableName = true;
        } else {
            $joinsStatement = '';
        }
        
        if (!isset($this->columnSelectDecorator)) {
            $component = new Component("SELECT");
        } else {
            $component = $this->columnSelectDecorator;
        }
        
        $this->columnSelectDecorator = new ColumnSelect($component, $this, $attachTableName);
        
        $sql = "\n\t{$this->columnSelectDecorator->getStatement()}\n\tFROM {$this->getTableName()}";
        
        $sql .= $joinsStatement;
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $sql .= $orderByStatement;
        }
        
        return new Component($sql);
    }

}
