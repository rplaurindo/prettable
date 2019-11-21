<?php

namespace Models\PDO\Paginables;

use
    QueryStatements\Decorators\Select\Pagination\MySQL
;

trait MySQLTrait {

    function readAll($limit = null, $pageNumber = 1) {
        $component = parent::readAllComponent();
        $component = new MySQL($component, $limit, $pageNumber);
        
        $sql = $component->getStatement();
        
        return $this->execute($sql)->fetchAll();
    }

    function readFrom($modelName, $limit = null, $pageNumber = 1) {
        $component = parent::readFromComponent($modelName);
        $component = new MySQL($component, $limit, $pageNumber);
        
        $sql = $component->getStatement();
        
        $this->setBindings([$this->primaryKeyValue]);
        
        return $this->execute($sql)->fetchAll();
    }

}
