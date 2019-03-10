<?php

namespace PReTTable\QueryStatements;

abstract class AbstractSelectDecorator extends AbstractSelectComponent {
    
    protected $_component;

    function __construct(AbstractSelectComponent $component) {
        $this->_component = $component;
    }

    protected function mountStatement() {
        $statement = $this->getStatement();
        
        return "{$this->_component->getStatement()}
            $statement";
    }
    
    abstract function getRersult();

}
