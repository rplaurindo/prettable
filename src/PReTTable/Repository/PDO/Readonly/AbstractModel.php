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
        
        $this->selectDecorator = new Select($this->selectDecorator, $this);
        
        $queryStatement = "
        {$this->selectDecorator->mountStatement()}
        
        FROM {$this->getTableName()}";

        if (isset($this->joinsDecorator)) {
            $queryStatement .= "\n\n\t{$this->joinsDecorator->mountStatement()}";
        }
        
        $orderByStatement = $this->getOrderByStatement();
        
        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }
        
        return new Component($queryStatement);
    }

}
