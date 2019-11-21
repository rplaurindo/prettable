<?php

namespace PreTTable\QueryStatements\Decorators\Select;

use
    PreTTable\QueryStatements\AbstractComponent
;

abstract class AbstractDecorator extends AbstractComponent {

    protected $_component;

    function __construct(AbstractComponent $component) {
        $this->_component = $component;
    }

    function getStatement() {
        $currentStatement = $this->_component->getStatement();
        
        if (preg_match('/^SELECT$|^SELECT[ \n\t]+$/', $currentStatement)) {
            $statement = "$currentStatement\n\t\t$this->_statement";
        } else {
            $statement = "$currentStatement, $this->_statement";
        }

        return $statement;
    }

}
