<?php

namespace PReTTable\QueryStatements;

abstract class AbstractSelectDecorator implements SelectComponentInterface {
    
    protected $_component;

    function __construct(SelectComponentInterface $component) {
        $this->_component = $component;
    }

}
