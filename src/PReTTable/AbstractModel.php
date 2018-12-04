<?php

namespace PReTTable;

use Exception, PDO, PDOException;

abstract class AbstractModel {
    
    private $modelName;
    
    private $model;
    
    private $queryMap;
    
    private $host;
    
    private $connection;
    
    private $prepare;
    
    private $primaryKeyValue;

    function __construct($host, array $data) {
        $this->modelName = get_class($this);
        $this->model = Reflection::getDeclarationOf($this->modelName);
        
        $this->host = $host;
        
        $this->primaryKeyValue = null;
        
        Connection::setData($data);
        
        try {
            $this->queryMap = new QueryMap($this->modelName);
        } catch (Exception $e) {
            echo $e;
        }
    }
    
    function create(array $attributes) {
        $clone = $this->getClone();
        
        $map = $clone->queryMap->insert($attributes)->getMap();
        
        $insertInto = $map['insertInto'];
        $values = $map['values'];
        
        $query = "
            INSERT INTO $insertInto
            VALUES $values
        ";
        
        try {
            $clone->connection->beginTransaction();
            $clone->prepare = $clone->connection->prepare($query);
            $clone->prepare->execute();
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        if ($clone->model::isPrimaryKeySelfIncremental()) {
            $clone->primaryKeyValue = $clone->connection->lastInsertId();
        } else {
            $clone->primaryKeyValue = $attributes[$clone->model::getPrimaryKeyName()];
        }
        
        return $clone;
    }
    
    function createAssociation($modelName, ...$rows) {
        //         pegar o nome da chave associada através da reflexão para usar attachesAssociativeForeignKey e anexar primaryKeyValue
    }
    
    private function attachesAssociativeForeignKey($foreignKeyName, ...$rows) {
        foreach ($rows as $index => $attributes) {
            $attributes[$foreignKeyName] = $this->lastInsertedPrimaryKey;
            $rows[$index] = $attributes;
        }
        
        return $rows;
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
        $clone = $this->getClone();
        
        $map = $clone->queryMap->delete($columnName, ...$values)->getMap();
        
        $deleteFrom = $map['deleteFrom'];
        $where = $map['where'];
        
        $query = "
            DELETE FROM $deleteFrom
            WHERE $where
        ";
        
        try {
            $prepare = $clone->connection->prepare($query);
            $prepare->execute();
            $clone->rollBack();
        } catch (PDOException $e) {
            $clone->rollBack();
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
    
//     put proxy methods (from QueryMap) here to relate models (contains and isContained)

    function commit() {
        return $this->connection->commit();
    }
    
    function rollBack() {
        $this->connection->exec('ROLLBACK');
    }
    
    protected function getClone() {
        return clone $this;
    }
    
    protected function establishConnection($database, $host = null) {
        if (isset($host)) {
            $this->host = $host;
        }
        
        $connection = new Connection();
        $this->connection = $connection->establishConnection($this->host, $database);
    }
    
}
