<?php

namespace PReTTable\Repository\OnlyReading\PDO;

use
    Exception,
    PDO,
    PDOException,
    PReTTable\Connections,
    PReTTable\Connections\PDOConnection,
    PReTTable\QueryStatements\Select,
    PReTTable\QueryStatements\SelectComponent,
    PReTTable\Repository
;

abstract class AbstractModel extends Repository\AbstractModel {
    
    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);
        
        PDOConnection::setData($this->connectionData);
        
        $this->connectionContext = new Connections\StrategyContext(new PDOConnection($this->environment));
    }
    
    function getRow() {
        $clone = $this->getClone();
        
        $select = new Select($clone->modelName);
        $selectStatement = "SELECT {$select->getStatement()}";
        
        $primaryKeyName = $clone->getPrimaryKeyName();
        
        $queryStatement = "
            $selectStatement
            
            FROM $clone->tableName
            WHERE $primaryKeyName = :$primaryKeyName";
            
        try {
            echo "$queryStatement\n\n";
            $PDOstatement = $clone->connection->prepare($queryStatement);
            $PDOstatement->bindParam(":$primaryKeyName", $clone->primaryKeyValue);
            $PDOstatement->execute();
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        if (
            isset($result) &&
            gettype($result) == 'array' &&
            count($result)
            ) {
            return $result[0];
        }
                
        return null;
    }
    
//     retornar um objeto que possui o m�todo addDecorator, o qual dever� retornar um objeto que possui um m�todo de execu��o e assim, recursivamente, 
//     decorar a query e executar. Assim, como uma pizza, teremos os ingredientes padr�o para a query e os aditivos (pagina��o) podem ser adicionados 
//     estrategicamente (cada DBMS tem sua forma de paginar, diferente de um ORDER BY que � padr�o). 
    function getAll() {
        $select = new Select($this->modelName);
        
        $queryStatement = "
            SELECT {$select->getStatement(...$this->relationalSelectBuilding->getInvolvedModelNames())}
            
            FROM $this->tableName";
        
        $joinsStatement = "";
        
        $joins = $this->relationalSelectBuilding->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }
        
        if (!empty($joinsStatement)) {
            $queryStatement .= "
                $joinsStatement";
        }
        
        $orderByStatement = $this->getOrderBy();
        
        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
        }
        
        $component = new SelectComponent($queryStatement);
        $component->setConnection($this->connection);
        
        return $component;
    }
    
    function get($modelName, $limit = null, $pageNumber = 1) {
        $clone = $this->getClone();
        
        $relationalSelectBuilding = $clone->relationalSelectBuilding->build($modelName, $clone->primaryKeyValue);
        
        $select = $relationalSelectBuilding->getSelect();
        $from = $relationalSelectBuilding->getFrom();
        $whereClause = $relationalSelectBuilding->getWhereClause();
        
        $joinsStatement = "";
        
        $queryStatement = "
            SELECT $select
            
            FROM $from";
        
        $joins = $relationalSelectBuilding->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }
        
        if (!empty($joinsStatement)) {
            $queryStatement .= "
                $joinsStatement";
        }
        
        $queryStatement .= "
            WHERE $whereClause";
        
        $orderByStatement = $clone->getOrderBy();
        
        if (isset($orderByStatement)) {
            $queryStatement .= "
                $orderByStatement";
        }
        
        if (isset($limit)) {
            if (!$clone->strategyContextIsDefined) {
                throw new Exception('PReTTable\PaginableStrategyInterface wasn\'t defined.');
            }
            
            $queryStatement .= "
                {$clone->pagerStrategyContext->getStatement($limit, $pageNumber)}
            ";
        }
        
        try {
            echo "$queryStatement\n\n";
            
            $PDOstatement = $clone->connection->query($queryStatement);
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }
    
    function getParent($modelName) {
        $clone = $this->getClone();
        
        $relationalSelectBuilding = $clone->relationalSelectBuilding->build($modelName, $clone->primaryKeyValue);
        
        $select = $relationalSelectBuilding->getSelect();
        $from = $relationalSelectBuilding->getFrom();
        $whereClause = $relationalSelectBuilding->getWhereClause();
        
        $joinsStatement = "";
        
        $queryStatement = "
            SELECT $select
            
            FROM $from";
        
        $joins = $relationalSelectBuilding->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }
        
        if (!empty($joinsStatement)) {
            $queryStatement .= "
                $joinsStatement";
        }
        
        $queryStatement .= "
            WHERE $whereClause";
        
        try {
            echo "$queryStatement\n\n";
            $PDOstatement = $clone->connection->query($queryStatement);
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        if (
            isset($result) &&
            gettype($result) == 'array' &&
            count($result)
            ) {
            return $result[0];
        }
        
        return null;
    }

}
