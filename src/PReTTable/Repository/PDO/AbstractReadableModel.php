<?php

namespace PReTTable\Repository\PDO;

use
    Exception,
    PDO,
    PDOException,
    PReTTable\ConnectionContext,
    PReTTable\Connections\PDOConnection,
    PReTTable\QueryStatements\Select,
    PReTTable\Repository
;

abstract class AbstractReadableModel extends Repository\AbstractModel {
    
    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);
        
        PDOConnection::setData($this->connectionData);
        
        $this->connectionContext = new ConnectionContext(new PDOConnection($this->environment));
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
    
    function getAll($limit = null, $pageNumber = 1) {
        $clone = $this->getClone();
        
        $select = new Select($clone->modelName);
        
        $queryStatement = "
            SELECT {$select->getStatement(...$clone->relationalSelectBuilding->getInvolvedModelNames())}
            
            FROM $clone->tableName";
        
        $joinsStatement = "";
        
        $joins = $clone->relationalSelectBuilding->getJoins();
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
        
        $orderByStatement = $clone->getOrderBy();
        
        if (isset($orderByStatement)) {
            $queryStatement .= $orderByStatement;
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
    
    function get($modelName, $limit = null, $pageNumber = 1) {
        $clone = $this->getClone();
        
        $relationalSelectBuilding = $clone->relationalSelectBuilding->build($modelName);
        
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
        
        $relationalSelectBuilding = $clone->relationalSelectBuilding->build($modelName);
        
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
