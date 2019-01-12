<?php

namespace PReTTable\Repository;

use 
    Exception,
    PDO,
    PDOException,
    PReTTable\Connection,
    PReTTable\Reflection,
    PReTTable\PaginableStrategyInterface,
    PReTTable\PaginableStrategyContext,
    PReTTable\QueryStatementStrategyContext,
    PReTTable\QueryStatements\Strategies\PDO\InsertInto,
    PReTTable\QueryStatements\Strategies\PDO\Update,
    PReTTable\QueryStatements\Select
;

abstract class AbstractModel 
    implements 
        IdentifiableModelInterface, 
        WritableModelInterface {
    
    private $primaryKeyValue;
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $host;
    
    private $queryMap;
    
    private $connection;
    
    private $order;
    
    private $by;
    
    private $pagerStrategyContext;
    
    private $strategyContextIsDefined;

    function __construct($host, array $data) {
        $this->modelName = get_class($this);
        $this->model = Reflection::getDeclarationOf($this->modelName);
        $this->host = $host;
        
        $this->primaryKeyValue = null;
        
        Connection::setData($data);
        
        $this->queryMap = new QueryMap($this->modelName);
        
        $this->tableName = $this->model->getTableName();
        
        $this->pagerStrategyContext = new PaginableStrategyContext();
        
        $this->strategyContextIsDefined = false;
    }
    
    function setPrimaryKeyValue($value) {
        $this->primaryKeyValue = $value;
    }
    
//     a proxy to set strategy context
    function setPager(PaginableStrategyInterface $pagerStrategy) {
        $this->strategyContextIsDefined = true;
        
        $this->pagerStrategyContext->setStrategy($pagerStrategy);
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
        
        $strategy = new QueryStatementStrategyContext(
            new InsertInto($clone->modelName));
        
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
        
        if ($clone->model->isPrimaryKeySelfIncremental()) {
            $clone->primaryKeyValue = $clone->connection->lastInsertId();
        } else {
            $clone->primaryKeyValue = $attributes[$clone->model
                ->getPrimaryKeyName()];
        }
        
        return $clone;
    }
    
    function createAssociations($modelName, $primaryKeyValue, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap
            ->getAssociativeModelNameOf($modelName);

        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between $clone->model and $modelName.");
        }
            
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
        
        return $clone;
    }
    
    function getRow() {
        $clone = $this->getClone();
        
        $select = new Select();
        $selectStatement = "SELECT {$select->getStatement($clone->modelName)}";
        
        $primaryKeyName = $clone->model->getPrimaryKeyName();
        
        $query = "
            $selectStatement
            FROM $clone->tableName
            WHERE $primaryKeyName = :$primaryKeyName";
            
        try {
            $PDOstatement = $clone->connection->prepare($query);
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
        
        $select = new Select();
        
        $query = "
            SELECT {$select->getStatement($clone->modelName, ...$clone->queryMap->getInvolvedModelNames())}
            FROM $clone->tableName";
        
        $joinsStatement = "";
        
        $joins = $clone->queryMap->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }
        
        if (!empty($joinsStatement)) {
            $query .= "
                $joinsStatement
            ";
        }
        
        if (isset($clone->order)) {
            $query .= "
                {$clone->getMountedOrderBy()}";
        }
        
        if (isset($limit)) {
            if (!$clone->strategyContextIsDefined) {
                throw new Exception('PReTTable\PaginableStrategyInterface wasn\'t defined.');
            }
            
            $query .= "
                {$clone->pagerStrategyContext->getStatement($limit, $pageNumber)}
            ";
        }
        
        try {
            echo $query;
            $PDOstatement = $clone->connection->query($query);
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }
    
    function get($primaryKeyValue, $modelName, $limit = null, $pageNumber = 1) {
        $clone = $this->getClone();
        
        $queryMap = $clone->queryMap->select($primaryKeyValue, $modelName);
        
        $map = $queryMap->getMap();
        
        $select = $map['select'];
        $from = $map['from'];
        $whereClause = $map['where'];
        
        $joinsStatement = "";
        
        $query = "
            SELECT $select
            FROM $from";
        
        $joins = $queryMap->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }
        
        if (!empty($joinsStatement)) {
            $query .= "
                $joinsStatement
            ";
        }
        
        $query .= "
            WHERE $whereClause";
        
        if (isset($clone->order)) {
            $query .= "
                {$clone->getMountedOrderBy()}";
        }
        
        if (isset($limit)) {
            if (!$clone->strategyContextIsDefined) {
                throw new Exception('PReTTable\PaginableStrategyInterface wasn\'t defined.');
            }
            
            $query .= "
                {$clone->pagerStrategyContext->getStatement($limit, $pageNumber)}
            ";
        }
        
        try {
            echo $query;
            $PDOstatement = $clone->connection->query($query);
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }
    
    function update($primaryKeyValue, array $attributes) {
        $clone = $this->getClone();
        
        $update = new Update($clone->modelName);
        
        $primaryKeyName = $update->getPrimaryKeyName();

        $strategy = new QueryStatementStrategyContext($update);
        
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
    
    function updateAssociations($primaryKeyValue, $modelName, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap
            ->getAssociativeModelNameOf($modelName);
        
        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between $clone->model and $modelName.");
        }
      
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
            
            $clone->deleteAssociations($primaryKeyValue, $modelName);
            
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
    
    function deleteAssociations($primaryKeyValue, $modelName, ...$relatedKeyValues) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->queryMap
            ->getAssociativeModelNameOf($modelName);
        
        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between $clone->modelName and $modelName.");
        }
        
        $associativeModel = Reflection
            ::getDeclarationOf($associativeModelName);
        $associativeTableName = $associativeModel::getTableName();
        $foreignKeyName = $associativeModel
            ::getAssociativeKeys()[$clone->modelName];
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            if (count($relatedKeyValues)) {
                $relatedForeignKeyName = $associativeModel
                    ::getAssociativeKeys()[$modelName];
                    
                    $statement = "
                    DELETE FROM $associativeTableName
                    WHERE
                        $foreignKeyName = :$foreignKeyName
                        AND $relatedForeignKeyName = :$relatedForeignKeyName
                ";
                    
                foreach ($relatedKeyValues as $relatedKeyValue) {
                    $PDOstatement = $clone->connection->prepare($statement);
                    
                    $PDOstatement
                        ->bindParam(":$foreignKeyName", $primaryKeyValue);
                    
                    $PDOstatement
                        ->bindParam(":$relatedForeignKeyName", $relatedKeyValue);
                    
                    $PDOstatement->execute();
                }
            } else {
                $statement = "
                    DELETE FROM $associativeTableName
                    WHERE $foreignKeyName = :$foreignKeyName
                ";
                
                $PDOstatement = $clone->connection->prepare($statement);
                $PDOstatement->bindParam(":$foreignKeyName",
                    $primaryKeyValue);
                
                $PDOstatement->execute();
            }
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        return $clone;
    }
    
    function join($modelName, $associatedColumn) {
        $clone = $this->getClone();
        
        $clone->queryMap->join($modelName, $associatedColumn);
        
        return $clone;
    }
    
    function save() {
        return $this->connection->commit();
    }
    
    function setOrder($columnName, $by = '') {
        $this->order = $columnName;
        $this->by = $by;
    }
    
    protected function beginTransaction() {
        $this->connection->beginTransaction();
    }
    
    protected function rollBack() {
        $this->connection->exec('ROLLBACK');
    }
    
    protected function establishConnection($databaseSchema, $host = null) {
        if (!isset($databaseSchema)) {
            throw new Exception('A database schema should be passed.');
        }
        
        if (isset($host)) {
            $this->host = $host;
        }
        
        $connection = new Connection();
        $this->connection = $connection
            ->establishConnection($this->host, $databaseSchema);
    }
    
//     to comply the Prototype pattern
    protected function getClone() {
        return clone $this;
    }
    
    private static function attachesAssociativeForeignKey($foreignKeyName, 
                                                          $value, 
                                                          ...$rows) {
        foreach ($rows as $index => $attributes) {
            $attributes[$foreignKeyName] = $value;
            $rows[$index] = $attributes;
        }
        
        return $rows;
    }
    
    private function getMountedOrderBy() {
        if (count($this->queryMap->getInvolvedModelNames())) {
            $columnStatement = "$this->tableName.$this->order";
        } else {
            $columnStatement = $this->order;
        }
        
        return "
            ORDER BY $columnStatement $this->by";
    }
    
}
