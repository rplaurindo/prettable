<?php

namespace PReTTable;

use Exception, PDO, PDOException;

abstract class AbstractModel {
    
    private $host;
    
    private $connection;
    
    private $queryMap;

    function __construct($host, array $data) {
        $this->host = $host;
        
        Connection::setData($data);
        
        try {
            $this->queryMap = new QueryMap(get_class($this));
        } catch (Exception $e) {
            echo $e;
        }
    }
    
    function establishConnection($database, $host = null) {
        if (isset($host)) {
            $this->host = $host;
        }
        
        $connection = new Connection();
        $this->connection = $connection->establishConnection($this->host, $database);
    }
    
    function create(array $attributes) {
        $map = $this->queryMap->insert($attributes)->getMap();
        
        $insertInto = $map['insertInto'];
        $values = $map['values'];
        
        $query = "
            INSERT INTO $insertInto
            VALUES $values
        ";
        
        try {
            $prepare = $this->connection->prepare($query);
            $prepare->execute();
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        if ($this->queryMap->getModel()->isPrimaryKeySelfIncremental()) {
            return $this->connection->lastInsertId();
        }
        
        return true;
        
    }
    
    function update($primaryKeyValue, array $attributes) {
        $map = $this->queryMap->update($primaryKeyValue, $attributes)->getMap();
        
        $update = $map['update'];
        $set = $map['set'];
        $where = $map['where'];
        
        $query = "
            UPDATE $update
            SET $set
            WHERE $where
        ";
        
        try {
            $prepare = $this->connection->prepare($query);
            $prepare->execute();
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return true;
    }
    
    function delete($columnName, ...$values) {
        $map = $this->queryMap->delete($columnName, ...$values)->getMap();
        
        $deleteFrom = $map['deleteFrom'];
        $where = $map['where'];
        
        $query = "
            DELETE FROM $deleteFrom
            WHERE $where
        ";
        
        try {
            $prepare = $this->connection->prepare($query);
            $prepare->execute();
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return true;
    }
    
    function getRow($columnName, $value = null) {
        $map = $this->queryMap->getRow($columnName, $value)->getMap();
        
        $select = $map['select'];
        $from = $map['from'];
        $where = $map['where'];
        
        $query = "
            SELECT $select 
            FROM $from
            WHERE $where
        ";
        
        try {
            $PDOStatement = $this->connection->query($query);
            $PDOStatement->setFetchMode(PDO::FETCH_ASSOC);
            $result = $PDOStatement->fetchAll();
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        if (count($result)) {
            return $result[0]; 
        }
            
        return null;
    }
    
//     put proxy methods (from QueryMap) here to relate models
    
//     function createAssociation($primaryKeyValue, $associationModelName,
//         $attributes, $associationAttributes) {
    function createAssociation($associativeModelName, $attributes) {
        
    }
    
    private function getClone() {
        return clone $this;
    }
    
}


