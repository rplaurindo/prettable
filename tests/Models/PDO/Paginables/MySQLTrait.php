<?php

namespace Models\PDO\Paginables;

use
    PReTTable\QueryStatements\Decorators\Select\Pagination\MySQL
;

trait MySQLTrait {
    
    function readAll($limit = null, $pageNumber = 1) {
        $component = parent::readAll();
        
        $component = new MySQL($component, $limit, $pageNumber);
        
        $queryStatement = $component->getStatement();
        
        return $this->execute($queryStatement);
    }

    function readFrom($modelName, $limit = null, $pageNumber = 1) {
        $component = parent::readFrom($modelName);

        return new MySQL($component, $limit, $pageNumber);
    }

}
