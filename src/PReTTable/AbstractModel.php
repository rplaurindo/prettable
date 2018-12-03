<?php

namespace PReTTable;

use Exception, PDO, PDOException;

abstract class AbstractModel {
    
    private $modelName;
    
    private $model;
    
    private $host;
    
    private $connection;
    
    private $queryMap;
    
    private $lastInsertedPrimaryKeyValue;

    function __construct($host, array $data) {
        $this->modelName = get_class($this);
        $this->model = Reflection::getDeclarationOf($this->modelName);
        $this->host = $host;
        $this->lastInsertedPrimaryKeyValue = null;
        
        Connection::setData($data);
        
        try {
            $this->queryMap = new QueryMap($this->modelName);
        } catch (Exception $e) {
            echo $e;
        }
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
            $this->rollBack();
            throw new PDOException($e);
        }
        
        if ($this->model::isPrimaryKeySelfIncremental()) {
            $this->lastInsertedPrimaryKeyValue = $this->connection->lastInsertId();
        } else {
            $this->lastInsertedPrimaryKeyValue = $attributes[$this->model::getPrimaryKeyName()];
        }
        
        return $this;
    }
    
//     o próprio QueryMap com seu método createAssociation avaliará, através de suas propriedade contains deste modelo, o nome da tabela associativa
    function createAssociation($modelName, ...$rows) {
//         pegar o nome da chave associada através da reflexão
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
//             $prepare->execute();
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
    
    protected function rollBack() {
        $this->connection->exec('ROLLBACK');
    }
    
    
//     put proxy methods (from QueryMap) here to relate models (contains and isContained)
    
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
