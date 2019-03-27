<?php

namespace PReTTable\QueryStatements\Select;

use
    PDO,
    PDOException,
    PReTTable\QueryStatements
;

abstract class AbstractDecorator extends QueryStatements\AbstractDecorator {

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
