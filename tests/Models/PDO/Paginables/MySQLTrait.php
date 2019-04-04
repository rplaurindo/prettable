<?php

namespace Models\PDO\Paginables;

use
    PReTTable\QueryStatements\Select\Decorators\PDO\Pagination\MySQL
;

trait MySQLTrait {
    
    function readAll($limit = null, $pageNumber = 1) {
        $component = parent::readAll();

        $clone = $this->getClone();
        
        $clone->queryComponent = new MySQL($component, $limit, $pageNumber);
        
        return $this;
    }

    function readFrom($modelName, $limit = null, $pageNumber = 1) {
        $component = parent::readFrom($modelName);

        return new MySQL($component, $limit, $pageNumber);
    }

}
