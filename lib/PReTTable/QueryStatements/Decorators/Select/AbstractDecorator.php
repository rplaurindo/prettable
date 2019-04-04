<?php

namespace PReTTable\QueryStatements\Decorators\Select;

use
    PReTTable\QueryStatements\AbstractComponent
;

abstract class AbstractDecorator extends AbstractComponent {

    protected $_component;

    function __construct(AbstractComponent $component) {
        $this->_component = $component;
    }

    function mountStatement() {
        $currentStatement = $this->_component->getStatement();
        
        if ($currentStatement[strlen($currentStatement) - 1] == ' ') {
            return "$currentStatement{$this->getStatement()}";
        }
        
        return "$currentStatement, {$this->getStatement()}";
    }

}
