<?php

namespace PReTTable\QueryStatements\Select\PDO;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements
;

abstract class AbstractPaginationDecorator extends QueryStatements\AbstractDecorator {

    protected $limit;

    protected $pageNumber;

    function __construct(QueryStatements\AbstractComponent $component) {
        $this->_component = $component;
    }

    function mountStatement() {
        $statement = $this->getStatement();

        return "{$this->_component->getStatement()}
            $statement";
    }

    function getRersult() {
        $queryStatement = $this->mountStatement();

        echo "$queryStatement\n\n";

        try {
            $PDOstatement = $this->_component->getConnection()->query($queryStatement);
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }

        return $result;
    }

}