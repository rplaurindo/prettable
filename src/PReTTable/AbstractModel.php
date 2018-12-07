<?php

namespace PReTTable;

use Exception, PDO, PDOException;

abstract class AbstractModel {
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $host;
    
    private $primaryKeyValue;
    
    private $queryMap;
    
    private $connection;
    
    private $prepare;

    function __construct($host, array $data) {
        $this->modelName = get_class($this);
        $this->model = Reflection::getDeclarationOf($this->modelName);
        $this->tableName = $this->model::getTableName();
        
        $this->host = $host;
        
        $this->primaryKeyValue = null;
        
        Connection::setData($data);
        
        try {
            $this->queryMap = new PDOStatementQueryMap($this->modelName);
        } catch (Exception $e) {
            echo $e;
        }
    }
    
    function contains($modelName, $associatedColumn) {
        $this->queryMap->contains($modelName, $associatedColumn);
    }
    
    function isContained($modelName, $associatedColumn) {
        $this->queryMap->isContained($modelName, $associatedColumn);
    }
    
    function containsThrough($modelName, $through) {
        $this->queryMap->containsThrough($modelName, $through);
    }
    
    function create(array $attributes) {
        $clone = $this->getClone();
        
        $insertIntoStatement = new PDOInsertIntoStatement($clone->modelName);
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            foreach ($insertIntoStatement->getStatements($attributes) as $statement) {
                $PDOstatement = $this->connection->prepare($statement);
                foreach ($attributes as $columnName => $value) {
//                     another params can be passed to make validations. A map of column name => data type can be defined by a interface to validate type, 
//                     for example. So this block can be moved to a external class.
                    $PDOstatement->bindParam(":$columnName", $value);
                }
                $PDOstatement->execute();
            }
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
        $clone = $this->getClone();
        
        if (isset($clone->primaryKeyValue)) {
            $associativeModelName = $this->queryMap->getAssociativeModelNameOf($modelName);
            $associativeModel = Reflection::getDeclarationOf($associativeModelName);
            $foreignKey = $associativeModel::getAssociativeKeys()[$clone->modelName];
        
            $rows = self::attachesAssociativeForeignKey($foreignKey, $this->primaryKeyValue, ...$rows);
        }
        
        $map = $clone->queryMap->insertIntoAssociation($modelName, ...$rows)->getMap();
        
        $insertInto = $map['insertInto'];
        $values = $map['values'];
        
        $query = "
            INSERT INTO $insertInto
            VALUES $values
        ";
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $clone->prepare = $clone->connection->prepare($query);
            $clone->prepare->execute();
            
            $clone->commit();
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        return true;
    }
    
    function updateAssociation() {
        
//         $associativeModelName = $this->queryMap->getAssociativeModelNameOf($modelName);
//         if (is_subclass_of($associativeModelName, 'IdentifiableModelInterface')) {
//             if ($associativeModel::isPrimaryKeySelfIncremental()) {
//                 $clone->primaryKeyValue = $clone->connection->lastInsertId();
//             } else {
//                 $clone->primaryKeyValue = $attributes[$clone->model::getPrimaryKeyName()];
//             }
//         }

    }
    
    function update($primaryKeyValue, array $attributes) {
        $clone = $this->getClone();

        $updateStatement = new PDOUpdateStatement($clone->modelName, $attributes);
        $primaryKeyName = $updateStatement->getPrimaryKeyName();
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $PDOstatement = $this->connection->prepare($updateStatement->getStatement());
            foreach ($attributes as $columnName => $value) {
                $PDOstatement->bindParam(":$columnName", $value);
            }
            $PDOstatement->bindParam(":$primaryKeyName", $primaryKeyValue);
            
            $PDOstatement->execute();
            
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        $clone->primaryKeyValue = $primaryKeyValue;
        
        return $clone;
    }
    
    function delete($columnName, ...$values) {
        $clone = $this->getClone();

        $statement = "
            DELETE FROM $clone->tableName
            WHERE $columnName = :$columnName
        ";
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            foreach ($values as $value) {
                $PDOstatement = $this->connection->prepare($statement);
                $PDOstatement->bindParam(":$columnName", $value);
                $PDOstatement->execute();
            }
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        return $clone;
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
    
    function commit() {
        return $this->connection->commit();
    }
    
    protected function beginTransaction() {
        $this->connection->beginTransaction();
    }
    
    protected function rollBack() {
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
    
    private static function attachesAssociativeForeignKey($foreignKeyName, $value, ...$rows) {
        foreach ($rows as $index => $attributes) {
            $attributes[$foreignKeyName] = $value;
            $rows[$index] = $attributes;
        }
        
        return $rows;
    }
    
}
