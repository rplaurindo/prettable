<?php

namespace PReTTable\QueryStatements;

abstract class AbstractDecorator extends AbstractComponent {
    
    protected $_component;

    function __construct(AbstractComponent $component) {
        $this->_component = $component;
    }

    protected function mountStatement() {
        $statement = $this->getStatement();
        
        return "{$this->_component->getStatement()}
            $statement";
    }
    
    abstract function getRersult();

}
