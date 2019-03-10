<?php

namespace PReTTable\QueryStatements\PDO;

use 
    PDO,
    PDOException
;

abstract class AbstractSelectPaginationDecorator extends AbstractSelectComponent {
    
    protected $limit;
    
    protected $pageNumber;
    
    private $_component;

    function __construct(AbstractSelectComponent $component) {
        $this->_component = $component;
    }

    function mountStatement() {
        $statement = $this->getStatement();
        
        return "{$this->_component->getStatement()}
            $statement";
    }
    
    function getRersult() {
        $queryStatement = $this->mountStatement();
        
        try {
            echo "$queryStatement\n\n";
            
            $PDOstatement = $this->_component->getConnection()->query($queryStatement);
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }

        return $result;
    }

}
