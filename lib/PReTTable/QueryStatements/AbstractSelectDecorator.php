<?php

namespace PReTTable\QueryStatements;

abstract class AbstractSelectDecorator implements SelectComponentInterface {

    function __construct(SelectComponentInterface $component) {
        
    }

}
