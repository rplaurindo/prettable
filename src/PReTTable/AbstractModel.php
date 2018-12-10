<?php

namespace PReTTable;

use 
    Exception, 
    PDO,
    PDOException,
    PReTTable\QueryStatements\Strategies\PDO\InsertInto,
    PReTTable\QueryStatements\Strategies\PDO\Update,
    PReTTable\QueryStatements\Select;

abstract class AbstractModel {
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $host;
    
    private $primaryKeyValue;
    
    private $queryMap;
    
    private $connection;
    
    private $orderBy;
    
    private $pager;

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
    
    function setPager(PaginableInterface $pager) {
        $this->pager = $pager;
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
        
        $strategy = new QueryStatementStrategyContext(new InsertInto($clone
            ->modelName));
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $statement = $strategy->getStatement($attributes);
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
            $clone->primaryKeyValue = $attributes[$clone->model
                ::getPrimaryKeyName()];
        }
        
        return $clone;
    }
    
    function createAssociations($modelName, $primaryKeyValue, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap->getAssociativeModelNameOf($modelName);
        
        if (isset($associativeModelName)) {            
            $associativeModel = Reflection::getDeclarationOf($associativeModelName);
            $foreignKeyName = $associativeModel::getAssociativeKeys()[$clone->modelName];
            
            if (gettype($primaryKeyValue) == 'array') {
                array_push($rows, $primaryKeyValue);
                $primaryKeyValue = $clone->primaryKeyValue;
            }
            
            $rows = self::attachesAssociativeForeignKey($foreignKeyName, 
                                                        $primaryKeyValue, 
                                                        ...$rows);
            
            $strategy = new QueryStatementStrategyContext(
                new InsertInto($associativeModelName));
            
            try {
                if (!$clone->connection->inTransaction()) {
                    $clone->beginTransaction();
                }
                
                foreach ($rows as $attributes) {
                    $statement = $strategy->getStatement($attributes);
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
        }
        
        
        return $clone;
    }
    
    function update($primaryKeyValue, array $attributes) {
        $clone = $this->getClone();
        
        $primaryKeyName = $updateStatement->getPrimaryKeyName();

        $strategy = new QueryStatementStrategyContext(new Update($clone
            ->modelName));
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $PDOstatement = $clone->connection->prepare($strategy
                ->getStatement($attributes));
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
        
        $associativeModelName = $clone->queryMap
            ->getAssociativeModelNameOf($modelName);
        
        if (isset($associativeModelName)) {            
            $associativeModel = Reflection
                ::getDeclarationOf($associativeModelName);
            $foreignKeyName = $associativeModel
                ::getAssociativeKeys()[$clone->modelName];
            
            if (gettype($primaryKeyValue) == 'array') {
                array_push($rows, $primaryKeyValue);
                $primaryKeyValue = $clone->primaryKeyValue;
            }
            
            $rows = self::attachesAssociativeForeignKey($foreignKeyName, 
                                                        $primaryKeyValue, 
                                                        ...$rows);
            
            $strategy = new QueryStatementStrategyContext(
                new InsertInto($associativeModelName));
            
            try {
                if (!$clone->connection->inTransaction()) {
                    $clone->beginTransaction();
                }
                
                $clone->deleteAssociations($modelName, $primaryKeyValue);
                
                foreach ($rows as $attributes) {
                    $statement = $strategy->getStatement($attributes);
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
        }
        
        
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
        
        $associativeModelName = $clone->queryMap
            ->getAssociativeModelNameOf($modelName);
        
        if (isset($associativeModelName)) {            
            $associativeModel = Reflection
                ::getDeclarationOf($associativeModelName);
            $associativeTableName = $associativeModel::getTableName();
            $foreignKeyName = $associativeModel
                ::getAssociativeKeys()[$clone->modelName];
            
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
                    $PDOstatement->bindParam(":$foreignKeyName", 
                                             $foreignKeyValue);
                    $PDOstatement->execute();
                }
            } catch (PDOException $e) {
                $clone->rollBack();
                echo $e;
                throw new PDOException($e);
            }
        }
        
        return $clone;
    }
    
    function deleteFromAssociation($modelName, $primaryKeyValue, 
                                   ...$relatedKeyValues) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap
            ->getAssociativeModelNameOf($modelName);
        
        if (isset($associativeModelName)) {
            $associativeModel = Reflection
                ::getDeclarationOf($associativeModelName);
            $associativeTableName = $associativeModel::getTableName();
            $foreignKeyName = $associativeModel
                ::getAssociativeKeys()[$clone->modelName];
            $relatedForeignKeyName = $associativeModel
                ::getAssociativeKeys()[$modelName];
            
            if (isset($clone->primaryKeyValue)) {
                $primaryKeyValue = $clone->primaryKeyValue;
            }
            
            $statement = "
                DELETE FROM $associativeTableName
                WHERE 
                    $foreignKeyName = :$foreignKeyName 
                    AND $relatedForeignKeyName = :$relatedForeignKeyName
            ";
            
            try {
                if (!$clone->connection->inTransaction()) {
                    $clone->beginTransaction();
                }
                
                foreach ($relatedKeyValues as $relatedKeyValue) {
                    $PDOstatement = $clone->connection->prepare($statement);
                    $PDOstatement
                        ->bindParam(":$foreignKeyName", $primaryKeyValue);
                    $PDOstatement->bindParam(":$relatedForeignKeyName", 
                                             $relatedKeyValue);
                    $PDOstatement->execute();
                }
            } catch (PDOException $e) {
                $clone->rollBack();
                echo $e;
                throw new PDOException($e);
            }
        }
        
        return $clone;
    }
    
    function getAssociatedKeys($modelName, $primaryKeyValue) {
        $clone = $this->getClone();
        $result = [];
        
        $associativeModelName = $clone->queryMap
            ->getAssociativeModelNameOf($modelName);
        
        if (isset($associativeModelName)) {
            $associativeModel = Reflection
                ::getDeclarationOf($associativeModelName);
            $associativeTableName = $associativeModel::getTableName();
            
            $foreignKeyName = $associativeModel
                ::getAssociativeKeys()[$clone->modelName];
            $relatedForeignKeyName = $associativeModel
                ::getAssociativeKeys()[$modelName];
            
            if (isset($clone->primaryKeyValue)) {
                $primaryKeyValue = $clone->primaryKeyValue;
            }
            
//             $joins = implode("\n            INNER JOIN ", $map['joins']);
            
            $statement = "
                SELECT $relatedForeignKeyName
                FROM $associativeTableName
                WHERE $foreignKeyName = :$foreignKeyName
            ";
            
            try {
                if (!$clone->connection->inTransaction()) {
                    $clone->beginTransaction();
                }
                
                $PDOstatement = $clone->connection->prepare($statement);
                $PDOstatement->bindParam(":$foreignKeyName", $primaryKeyValue);            
                $PDOstatement->execute();
                
                $result = $PDOstatement
                    ->fetchAll(PDO::FETCH_COLUMN, $relatedForeignKeyName);
            } catch (PDOException $e) {
                $clone->rollBack();
                echo $e;
                throw new PDOException($e);
            }
        }
        
        return $result;
    }
    
    function getRow($columnName, $value = null) {
        $clone = $this->getClone();

//         func_get_args()
        if (empty($value)) {
            $value = $columnName;
            
            $primaryKeyName = $clone->model::getPrimaryKeyName();
            $columnName = $primaryKeyName;
        }
        
        $selectStatement = new Select($clone->modelName);
        $selectStatement = $selectStatement->getStatement();
        
        $query = "
            SELECT $selectStatement
            FROM $clone->tableName
            WHERE $columnName = :$columnName
        ";
        
        try {
            $PDOstatement = $clone->connection->prepare($query);
            $PDOstatement->bindParam(":$columnName", $value);
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
    
    function setOrder($columnName, $by = '') {
        $this->orderBy = "\nORDER BY $columnName $by";
    }
    
    function getAll($limit = null, $page = 1) {
//         $paginationStatement;
//         testar se h� limit, caso sim, paginar. Se page n�o for informado, a p�gina ser� a primeira por padr�o 
        return [];
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
        $this->connection = $connection->establishConnection($this->host, 
                                                             $database);
    }
    
    private static function attachesAssociativeForeignKey($foreignKeyName, 
                                                          $value, ...$rows) {
        foreach ($rows as $index => $attributes) {
            $attributes[$foreignKeyName] = $value;
            $rows[$index] = $attributes;
        }
        
        return $rows;
    }
    
}
