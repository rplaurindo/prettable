<?php

namespace PReTTable\QueryStatements\Select\PDO;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements\AbstractComponent,
    PReTTable\QueryStatements\AbstractDecorator
;

abstract class AbstractPaginationDecorator extends AbstractDecorator {

    protected $limit;

    protected $pageNumber;

    function __construct(AbstractComponent $component) {
        $this->_component = $component;
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
