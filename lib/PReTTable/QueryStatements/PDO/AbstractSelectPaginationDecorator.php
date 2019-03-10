<?php

namespace PReTTable\QueryStatements\PDO;

use 
    PDO,
    PDOException,
    PReTTable\QueryStatements\AbstractSelectComponent
;

abstract class AbstractSelectPaginationDecorator extends AbstractSelectComponent {
    
    private $_component;
    
    private $_connection;

    function __construct(AbstractSelectComponent $component) {
        $this->_component = $component;
    }
    
    function getStatement($limit, $pageNumber = 1) {
        $statement = parent::getStatement();
        
        return "$statement
            {$this->_component->getStatement()}";
    }
    
    function setConnection(PDO $connection) {
        $this->_connection = $connection;
    }
    
    function execute() {
        $queryStatement = $this->_component->getStatement();
        
        try {
            echo "$queryStatement\n\n";
            
            $PDOstatement = $this->_connection->query($queryStatement);
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }

        return $result;
    }

}
