<?php

namespace PreTTable\QueryStatements;

abstract class AbstractDecorator extends AbstractComponent {

    protected $_component;

    function __construct(AbstractComponent $component) {
        $this->_component = $component;
    }

    function getStatement() {
        return "{$this->_component->getStatement()}\n\n\t$this->_statement";
    }

}
