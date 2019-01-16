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
    
    private $modelName;
    
    private $model;
    
    private $tableName;
    
    private $host;
    
    private $relationshipBuilding;
    
    private $relationalSelectBuilding;
    
    private $connection;
    
    private $order;
    
    private $by;
    
    private $pagerStrategyContext;
    
    private $strategyContextIsDefined;

    function __construct($host, array $data) {
        $this->modelName = get_class($this);
        $this->model = Reflection::getDeclarationOf($this->modelName);
        $this->host = $host;
        
        Connection::setData($data);
        
        $this->relationshipBuilding = new RelationshipBuilding($this->modelName);
        $this->relationalSelectBuilding = new RelationalSelectBuilding($this->relationshipBuilding);
        
        $this->tableName = $this->model->getTableName();
        
        $this->pagerStrategyContext = new PaginableStrategyContext();
        
        $this->strategyContextIsDefined = false;
    }
    
    function setPrimaryKeyValue($value) {
        $this->relationshipBuilding->setPrimaryKeyValue($value);
    }
    
//     a proxy to set strategy context
    function setPager(PaginableStrategyInterface $pagerStrategy) {
        $this->strategyContextIsDefined = true;
        
        $this->pagerStrategyContext->setStrategy($pagerStrategy);
    }
    
    function contains($modelName, $associatedColumn) {
        $this->relationshipBuilding->contains($modelName, $associatedColumn);
    }
    
    function isContained($modelName, $associatedColumn) {
        $this->relationshipBuilding->isContained($modelName, $associatedColumn);
    }
    
    function containsThrough($modelName, $through) {
        $this->relationshipBuilding->containsThrough($modelName, $through);
    }
    
    function create(array $attributes) {
        $clone = $this->getClone();
        
        $strategy = new QueryStatementStrategyContext(
            new InsertInto($clone->modelName));
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $PDOstatement = $clone->connection
                ->prepare($strategy->getStatement($attributes));
            foreach ($attributes as $columnName => $value) {
//                 another params can be passed to make validations. A map of column name => data type can be defined by a interface to validate type,
//                 for example. So this block can be moved to a external class.
                $PDOstatement->bindValue(":$columnName", $value);
            }
            $PDOstatement->execute();
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        if ($clone->model->isPrimaryKeySelfIncremental()) {
            $clone->relationshipBuilding
                ->setPrimaryKeyValue($clone->connection->lastInsertId());
        } else {
            $clone->relationshipBuilding->setPrimaryKeyValue($attributes[$clone
                ->model->getPrimaryKeyName()]);
        }
        
        return $clone;
    }
    
    function createAssociations($modelName, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->relationshipBuilding
            ->getAssociativeModelNameOf($modelName);

        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between $clone->modelName and $modelName.");
        }
            
        $associativeModel = Reflection
            ::getDeclarationOf($associativeModelName);
        $foreignKeyName = $associativeModel
            ::getAssociativeKeys()[$clone->modelName];
        
        $rows = self::attachesAssociativeForeignKey($foreignKeyName, 
                                                    $clone->relationshipBuilding->getPrimaryKeyValue(), 
                                                    ...$rows);
        
        $strategy = new QueryStatementStrategyContext(
            new InsertInto($associativeModelName));
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            foreach ($rows as $attributes) {
                $PDOstatement = $clone->connection
                    ->prepare($strategy->getStatement($attributes));
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
            $PDOstatement->bindParam(":$primaryKeyName", $clone->relationshipBuilding->getPrimaryKeyValue());
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
            SELECT {$select->getStatement($clone->modelName, ...$clone->relationalSelectBuilding->getInvolvedModelNames())}
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
            $query .= "
                $joinsStatement";
        }
        
        if (isset($clone->order)) {
            $query .= "{$clone->getMountedOrderBy()}";
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
            echo "$query\n";
            $PDOstatement = $clone->connection->query($query);
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }
    
    function get($modelName, $limit = null, $pageNumber = 1) {
        $clone = $this->getClone();
        
        $relationalSelectMap = $clone->relationalSelectBuilding->map($modelName);
        
        $select = $relationalSelectMap->getSelect();
        $from = $relationalSelectMap->getFrom();
        $whereClause = $relationalSelectMap->getWhereClause();
        
        $joinsStatement = "";
        
        $query = "
            SELECT $select
            
            FROM $from";
        
        $joins = $relationalSelectMap->getJoins();
        if (count($joins)) {
            $joinsStatement .= "
            INNER JOIN " .
            implode("
            INNER JOIN ", $joins);
        }
        
        if (!empty($joinsStatement)) {
            $query .= "
                $joinsStatement";
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
            echo "$query\n\n";
            $PDOstatement = $clone->connection->query($query);
            
            $result = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new PDOException($e);
        }
        
        return $result;
    }
    
    function update(array $attributes) {
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
                $PDOstatement->bindValue(":$columnName", $value);
            }
            $PDOstatement->bindParam(":$primaryKeyName", $clone->relationshipBuilding->getPrimaryKeyValue());
            
            $PDOstatement->execute();
            
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        return $clone;
    }
    
    function updateAssociations($modelName, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->relationshipBuilding
            ->getAssociativeModelNameOf($modelName);
        
        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between $clone->modelName and $modelName.");
        }
      
        $associativeModel = Reflection
            ::getDeclarationOf($associativeModelName);
        $foreignKeyName = $associativeModel
            ::getAssociativeKeys()[$clone->modelName];
        
        $rows = self::attachesAssociativeForeignKey($foreignKeyName, 
                                                    $clone->relationshipBuilding->getPrimaryKeyValue(), 
                                                    ...$rows);
        
        $strategy = new QueryStatementStrategyContext(
            new InsertInto($associativeModelName));
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $clone->deleteAssociations($modelName);
            
            foreach ($rows as $attributes) {
                $PDOstatement = $clone->connection
                    ->prepare($strategy->getStatement($attributes));
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
    
    function delete() {
        $clone = $this->getClone();
        
        $primaryKeyName = $clone->getPrimaryKeyName();

        $query = "
            DELETE FROM $clone->tableName
            WHERE $primaryKeyName = :$primaryKeyName";
        
        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }
            
            $PDOstatement = $clone->connection->prepare($query);
            $PDOstatement->bindParam(":$primaryKeyName", $clone->relationshipBuilding->getPrimaryKeyValue());
            $PDOstatement->execute();
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }
        
        return $clone;
    }
    
    function deleteAssociations($modelName, ...$relatedKeyValues) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->relationshipBuilding
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
                
                $query = "
                    DELETE FROM $associativeTableName
                    WHERE
                        $foreignKeyName = :$foreignKeyName
                        AND $relatedForeignKeyName = :$relatedForeignKeyName
                ";
                    
                foreach ($relatedKeyValues as $relatedKeyValue) {
                    $PDOstatement = $clone->connection->prepare($query);
                    
                    $PDOstatement
                        ->bindParam(":$foreignKeyName", $clone->relationshipBuilding->getPrimaryKeyValue());
                    
                    $PDOstatement
                        ->bindParam(":$relatedForeignKeyName", $relatedKeyValue);
                    
                    $PDOstatement->execute();
                }
            } else {
                $query = "
                    DELETE FROM $associativeTableName
                    WHERE $foreignKeyName = :$foreignKeyName
                ";
                
                $PDOstatement = $clone->connection->prepare($query);
                $PDOstatement->bindParam(":$foreignKeyName",
                    $clone->relationshipBuilding->getPrimaryKeyValue());
                
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
        
        $clone->relationalSelectBuilding->join($modelName, $associatedColumn);
        
        $clone->relationalSelectBuilding->addsInvolvedModelNames($modelName);
        
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
        if (count($this->relationalSelectBuilding->getInvolvedModelNames())) {
            $columnStatement = "$this->tableName.$this->order";
        } else {
            $columnStatement = $this->order;
        }
        
        return "
            ORDER BY $columnStatement $this->by";
    }
    
}
