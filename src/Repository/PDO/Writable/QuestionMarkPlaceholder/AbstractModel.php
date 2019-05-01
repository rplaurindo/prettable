<?php

namespace Repository\PDO\Writable\QuestionMarkPlaceholder;

use
    Exception,
    PDOException,
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\Placeholders,
    PReTTable\QueryStatements\WriteStrategies\InsertInto,
    PReTTable\QueryStatements\WriteStrategies\Update,
    PReTTable\Reflection,
    PReTTable\WritableModelInterface,
    QueryStatements\Placeholders\Strategies\QuestionMark,
    Repository\PDO\Readonly\QuestionMarkPlaceholder
;

abstract class AbstractModel extends QuestionMarkPlaceholder\AbstractModel
    implements
        WritableModelInterface
{
    
    private $errorsStack;
    
    function __construct(array $connectionData, $environment = null) {
        parent::__construct($connectionData, $environment);
        
        $this->errorsStack = [];
    }

    function create(array $attributes) {
        $clone = $this->getClone();

        $insertStrategy = new QueryStatements\StrategyContext(
            new InsertInto($clone->getTableName()));

        $values = array_values($attributes);

        $placeholderStrategy =
            new Placeholders\StrategyContext(new QuestionMark());
        $attributes = $placeholderStrategy->getStatement($attributes);

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            $statement = $clone->connection
                ->prepare($insertStrategy->getStatement($attributes));

            foreach ($values as $index => $value) {
//                 another params can be passed to make validations. A map of column name => data type can be defined by a interface to validate type,
//                 for example. So this block can be moved to a external class.
                $statement->bindValue($index + 1, $value);
            }
            
            $statement->execute();
        } catch (PDOException $e) {
            $clone->putErrorOnStack($e);
        }

        if ($clone->isPrimaryKeySelfIncremental()) {
            $clone->setPrimaryKeyValue($clone->connection->lastInsertId());
        } else {
            $clone->setPrimaryKeyValue($attributes[$clone
                ->getPrimaryKeyName()]);
        }

        return $clone;
    }

    function createAssociations($modelName, ...$rows) {
        $clone = $this->getClone();

        $associativeModelName = $clone
            ->getAssociativeModelNameFrom($modelName);

        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between {$clone->name} and $modelName.");
        }

        $associativeModel = Reflection
            ::getDeclarationOf($associativeModelName);

        $associativeTableName = $associativeModel->getTableName();

        $foreignKeyName = $associativeModel
            ::getAssociativeColumnNames()[$clone->name];

        if ($clone->primaryKeyValue) {
            $rows = self::attachesAssociativeForeignKey($foreignKeyName,
                $clone->primaryKeyValue,
                ...$rows);
        }
        
        $insertStrategy = new QueryStatements\StrategyContext(
            new InsertInto($associativeTableName));

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            foreach ($rows as $attributes) {

                $values = array_values($attributes);

                $placeholderStrategy =
                    new Placeholders\StrategyContext(new QuestionMark());
                $attributes = $placeholderStrategy->getStatement($attributes);

                $statement = $clone->connection
                    ->prepare($insertStrategy->getStatement($attributes));

                foreach ($values as $index => $value) {
                    $statement->bindValue($index + 1, $value);
                }
                
                $statement->execute();
            }
        } catch (PDOException $e) {
            $clone->putErrorOnStack($e);
        }

        return $clone;
    }

    function update(array $attributes) {
        $clone = $this->getClone();

        $primaryKeyName = $clone->getPrimaryKeyName();

        $updateStrategy = new QueryStatements\StrategyContext(
            new Update($clone->getTableName(), $primaryKeyName, $clone->primaryKeyValue));

        $values = array_values($attributes);

        $placeholderStrategy =
            new Placeholders\StrategyContext(new QuestionMark());
        $attributes = $placeholderStrategy->getStatement($attributes);

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            $statement = $clone->connection
                ->prepare($updateStrategy->getStatement($attributes));

            foreach ($values as $index => $value) {
                $statement->bindValue($index + 1, $value);
            }

            $statement->execute();

        } catch (PDOException $e) {
            $clone->putErrorOnStack($e);
        }

        return $clone;
    }

    function updateAssociations($modelName, ...$rows) {
        $clone = $this->getClone();

        $clone = $clone->deleteAssociations($modelName);

        $clone = $clone->createAssociations($modelName, ...$rows);

        return $clone;
    }

    function delete() {
        $clone = $this->getClone();

        $primaryKeyName = $clone->getPrimaryKeyName();

        $queryStringStatement = "
            DELETE FROM {$clone->getTableName()}

            WHERE $primaryKeyName = ?";

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            $statement = $clone->connection->prepare($queryStringStatement);
            $statement->bindParam(1, $clone->primaryKeyValue);
            
            $statement->execute();
        } catch (PDOException $e) {
            $clone->putErrorOnStack($e);
        }

        return $clone;
    }

    function deleteAssociations($modelName) {
        $clone = $this->getClone();

        $associativeModelName = $clone
            ->getAssociativeModelNameFrom($modelName);

        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between {$clone->name} and $modelName.");
        }

        $associativeModel = Reflection
            ::getDeclarationOf($associativeModelName);

        $associativeTableName = $associativeModel->getTableName();

        $foreignKeyName = $associativeModel
            ::getAssociativeColumnNames()[$clone->name];

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            $queryStringStatement = "
                DELETE FROM $associativeTableName

                WHERE $foreignKeyName = ?";

            $statement = $clone->connection->prepare($queryStringStatement);
            $statement->bindParam(1, $clone->primaryKeyValue);

            $statement->execute();
        } catch (PDOException $e) {
            $clone->putErrorOnStack($e);
        }

        return $clone;
    }

    function save($quiet = true) {
        if (!count($this->errorsStack)) {
            $this->connection->commit();
            return true;
        }
        
        $this->connection->rollBack();
        
        if ($quiet) {
            return false;
        }
        
        $text = '';
        foreach ($this->errorsStack as $exception) {
            $text .= "\n#{$exception->getLine()} {$exception->getFile()} {$exception->getMessage()}";
        }
        
        throw new Exception("\n" . $text . "\n\n");
    }

    protected function beginTransaction() {
        $this->connection->beginTransaction();
    }

    protected function rollBack() {
        $this->connection->exec('ROLLBACK');
    }
    
    private function putErrorOnStack(PDOException $exception) {
        array_unshift($this->errorsStack, $exception);
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

}
