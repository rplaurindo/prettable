<?php

namespace PReTTable\Repository\PDO\Readonly;

use
    PReTTable\QueryStatements\Decorators\Select,
    PReTTable\QueryStatements\Component,
    PReTTable\Repository\PDO\AbstractModelBase
;

abstract class AbstractModel extends AbstractModelBase {

    function readAll() {
        if (!isset($this->selectDecorator)) {
            $this->selectDecorator = new Component('SELECT ');
        }
        
        $this->selectDecorator = new Select($this->selectDecorator, $this, true);
        
        $queryStatement = "
        {$this->selectDecorator->getStatement()}
        
        FROM {$this->getTableName()}";

        if (isset($this->joinsDecorator)) {
            $queryStatement .= "\t{$this->joinsDecorator->getStatement()}";
        }
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }
        
        return new Component($queryStatement);
    }

}
