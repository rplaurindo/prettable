<?php

namespace PReTTable\QueryStatements\PDO;

use
    PReTTable\QueryStatements
;

abstract class AbstractSelectComponent extends QueryStatements\AbstractSelectComponent {
    
    function __construct($statement) {
        parent::__construct($statement);
    }

}
