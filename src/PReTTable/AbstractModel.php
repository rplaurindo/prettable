<?php

namespace PReTTable;

use 
    Exception, 
    PDO, 
    PDOException,
    PReTTable\PDO\InsertIntoStatement,
    PReTTable\PDO\UpdateStatement;

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
        $this->host = $host;
        
        $this->primaryKeyValue = null;
        
        Connection::setData($data);
        
        try {
            $this->queryMap = new QueryMap($this->modelName);
        } catch (Exception $e) {
            echo $e;
            throw new Exception($e);
        }
        
        $this->tableName = $this->model::getTableName();
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
        
        $insertIntoStatement = new InsertIntoStatement($clone->modelName);
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $statement = $insertIntoStatement->getStatements($attributes);
            $PDOstatement = $clone->connection->prepare($statement);
            foreach ($attributes as $columnName => $value) {
//                 another params can be passed to make validations. A map of column name => data type can be defined by a interface to validate type,
//                 for example. So this block can be moved to a external class.
                $PDOstatement->bindParam(":$columnName", $value);
            }
            $PDOstatement->execute();
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
    
    function createAssociations($modelName, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap->getAssociativeModelNameOf($modelName);
        $associativeModel = Reflection::getDeclarationOf($associativeModelName);
        $foreignKeyName = $associativeModel::getAssociativeKeys()[$clone->modelName];
        
        if (isset($clone->primaryKeyValue)) {
            $rows = self::attachesAssociativeForeignKey($foreignKeyName, 
                                                        $clone->primaryKeyValue, 
                                                        ...$rows);
        }
        
        $insertIntoStatement = new InsertIntoStatement($associativeModelName);
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            foreach ($rows as $attributes) {
                $statement = $insertIntoStatement->getStatement($attributes);
                $PDOstatement = $clone->connection->prepare($statement);
                foreach ($attributes as $columnName => $value) {
                    $PDOstatement->bindValue(":$columnName", $value);
                }
                $PDOstatement->execute();
            }
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        return $clone;
    }
    
    function update($primaryKeyValue, array $attributes) {
        $clone = $this->getClone();

        $updateStatement = new UpdateStatement($clone->modelName);
        $primaryKeyName = $updateStatement->getPrimaryKeyName();
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $PDOstatement = $clone->connection->prepare($updateStatement->getStatement($attributes));
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
    
    function updateAssociations($modelName, $primaryKeyValue, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap->getAssociativeModelNameOf($modelName);
        $associativeModel = Reflection::getDeclarationOf($associativeModelName);
        $foreignKeyName = $associativeModel::getAssociativeKeys()[$clone->modelName];
        
        if (gettype($primaryKeyValue) == 'array') {
            $rows = array_merge($rows, $primaryKeyValue);
            $primaryKeyValue = $clone->primaryKeyValue;
        }
        
        $rows = self::attachesAssociativeForeignKey($foreignKeyName, $primaryKeyValue, ...$rows);
        
        $updateStatement = new UpdateStatement($clone->modelName, $attributes);
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
//             foreach ($rows as $attributes) {
//                 $statement = $insertIntoStatement->getStatement($attributes);
//                 $PDOstatement = $clone->connection->prepare($statement);
//                 foreach ($attributes as $columnName => $value) {
//                     $PDOstatement->bindValue(":$columnName", $value);
//                 }
//                 $PDOstatement->execute();
//             }
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        //         if (is_subclass_of($associativeModelName, 'IdentifiableModelInterface')) {
        //             if ($associativeModel::isPrimaryKeySelfIncremental()) {
        //                 $clone->primaryKeyValue = $clone->connection->lastInsertId();
        //             } else {
        //                 $clone->primaryKeyValue = $attributes[$clone->model::getPrimaryKeyName()];
        //             }
        //         }
        
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
                $PDOstatement = $clone->connection->prepare($statement);
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
    
    function deleteAssociations($modelName, ...$foreignKeyValues) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap->getAssociativeModelNameOf($modelName);
        $associativeModel = Reflection::getDeclarationOf($associativeModelName);
        $associativeTableName = $associativeModel::getTableName();
        $foreignKeyName = $associativeModel::getAssociativeKeys()[$clone->modelName];
        
        $statement = "
            DELETE FROM $associativeTableName
            WHERE $foreignKeyName = :$foreignKeyName
        ";
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            foreach ($foreignKeyValues as $foreignKeyValue) {
                $PDOstatement = $clone->connection->prepare($statement);
                $PDOstatement->bindParam(":$foreignKeyName", $foreignKeyValue);
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
        $clone = $this->getClone();

//         func_get_args()
        if (empty($value)) {
            $value = $columnName;
            
            $primaryKeyName = $clone->model::getPrimaryKeyName();
            $columnName = $primaryKeyName;
        }
        
        $selectStatement = new SelectStatement($clone->modelName);
        $selectStatement = $selectStatement->getStatement();
        
        $query = "
            SELECT $selectStatement
            FROM $clone->tableName
            WHERE $columnName = :$columnName
        ";
        
        try {
            $PDOStatement = $clone->connection->prepare($query);
            $PDOStatement->bindParam(":$columnName", $value);
            $PDOStatement->setFetchMode(PDO::FETCH_ASSOC);
            $PDOStatement->execute();
            $result = $PDOStatement->fetchAll();
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
