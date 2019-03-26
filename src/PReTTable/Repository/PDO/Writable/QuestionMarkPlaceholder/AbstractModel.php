<?php

namespace PReTTable\Repository\PDO\Writable\QuestionMarkPlaceholder;

use
    Exception,
    PDOException,
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\Placeholders\StrategyContext,
    PReTTable\QueryStatements\Placeholders\Strategies\QuestionMark,
    PReTTable\QueryStatements\WriteStrategies\InsertInto,
    PReTTable\QueryStatements\WriteStrategies\Update,
    PReTTable\Reflection,
    PReTTable\Repository\PDO\Readonly\QuestionMarkPlaceholder,
    PReTTable\WritableModelInterface
;

abstract class AbstractModel extends QuestionMarkPlaceholder\AbstractModel
    implements
        WritableModelInterface
{

    function create(array $attributes) {
        $clone = $this->getClone();

        $insertStrategy = new QueryStatements\StrategyContext(
            new InsertInto($clone->tableName));

        $values = array_values($attributes);
        $placeholderStrategy = new StrategyContext(new QuestionMark());
        $attributes = $placeholderStrategy->getStatement($attributes);

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            $PDOstatement = $clone->connection
                ->prepare($insertStrategy->getStatement($attributes));

            foreach ($values as $index => $value) {
//                 another params can be passed to make validations. A map of column name => data type can be defined by a interface to validate type,
//                 for example. So this block can be moved to a external class.
                $PDOstatement->bindValue($index + 1, $value);
            }

            $PDOstatement->execute();
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }

        if ($clone->model->isPrimaryKeySelfIncremental()) {
            $clone->setPrimaryKeyValue($clone->connection->lastInsertId());
        } else {
            $clone->setPrimaryKeyValue($attributes[$clone->getPrimaryKeyName()]);
        }

        return $clone;
    }

    function createAssociations($modelName, ...$rows) {
        $clone = $this->getClone();

        $associativeModelName = $clone->relationshipBuilding
            ->getAssociativeModelNameOf($modelName);

        if (!isset($associativeModelName)) {
            throw new Exception("There's no such relationship between $clone->name and $modelName.");
        }

        $associativeModel = Reflection
            ::getDeclarationOf($associativeModelName);

        $associativeTableName = $associativeModel->getTableName();

        $foreignKeyName = $associativeModel
            ::getAssociativeColumnNames()[$clone->name];
        $rows = self::attachesAssociativeForeignKey($foreignKeyName,
                                                    $clone->primaryKeyValue,
                                                    ...$rows);

        $strategy = new QueryStatements\StrategyContext(
            new InsertInto($associativeTableName));

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

    function update(array $attributes) {
        $clone = $this->getClone();

        $primaryKeyName = $clone->getPrimaryKeyName();

        $update = new Update($clone->tableName, $primaryKeyName);

        $strategy = new QueryStatements\StrategyContext($update);

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            $PDOstatement = $clone->connection->prepare($strategy
                ->getStatement($attributes));
            foreach ($attributes as $columnName => $value) {
                $PDOstatement->bindValue(":$columnName", $value);
            }
            $PDOstatement->bindParam(":$primaryKeyName", $clone->primaryKeyValue);

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

        $clone = $clone->deleteAssociations($modelName);

        $clone = $clone->createAssociations($modelName, ...$rows);

        return $clone;
    }

    function delete() {
        $clone = $this->getClone();

        $primaryKeyName = $clone->getPrimaryKeyName();

        $queryStatement = "
            DELETE FROM $clone->tableName
            WHERE $primaryKeyName = :$primaryKeyName";

        try {
            if (!$clone->connection->inTransaction()) {
                $clone->beginTransaction();
            }

            $PDOstatement = $clone->connection->prepare($queryStatement);
            $PDOstatement->bindParam(":$primaryKeyName", $clone->primaryKeyValue);
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
            throw new Exception("There's no such relationship between $clone->name and $modelName.");
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

            if (count($relatedKeyValues)) {
                $relatedForeignKeyName = $associativeModel
                    ::getAssociativeColumnNames()[$modelName];

                $queryStatement = "
                    DELETE FROM $associativeTableName
                    WHERE
                        $foreignKeyName = :$foreignKeyName
                        AND $relatedForeignKeyName = :$relatedForeignKeyName
                ";

                foreach ($relatedKeyValues as $relatedKeyValue) {
                    $PDOstatement = $clone->connection->prepare($queryStatement);

                    $PDOstatement
                        ->bindParam(":$foreignKeyName", $clone->primaryKeyValue);

                    $PDOstatement
                        ->bindParam(":$relatedForeignKeyName", $relatedKeyValue);

                    $PDOstatement->execute();
                }
            } else {
                $queryStatement = "
                    DELETE FROM $associativeTableName
                    WHERE $foreignKeyName = :$foreignKeyName
                ";

                $PDOstatement = $clone->connection->prepare($queryStatement);
                $PDOstatement->bindParam(":$foreignKeyName",
                    $clone->primaryKeyValue);

                $PDOstatement->execute();
            }
        } catch (PDOException $e) {
            $clone->rollBack();
            echo $e;
            throw new PDOException($e);
        }

        return $clone;
    }

    function save() {
        return $this->connection->commit();
    }

    protected function beginTransaction() {
        $this->connection->beginTransaction();
    }

    protected function rollBack() {
        $this->connection->exec('ROLLBACK');
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
