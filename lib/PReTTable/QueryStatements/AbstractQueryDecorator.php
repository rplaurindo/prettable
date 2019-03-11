<?php

namespace PReTTable\QueryStatements;

abstract class AbstractQueryDecorator extends AbstractQueryComponent {
    
    protected $_component;

    function __construct(AbstractQueryComponent $component) {
        $this->_component = $component;
    }

    protected function mountStatement() {
        $statement = $this->getStatement();
        
        return "{$this->_component->getStatement()}
            $statement";
    }
    
    abstract function getRersult();

}
