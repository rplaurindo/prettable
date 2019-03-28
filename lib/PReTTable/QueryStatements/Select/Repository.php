<?php

namespace PReTTable\QueryStatements\Select;

use
    PReTTable\Reflection,
    PReTTable\InheritanceRelationship,
    PReTTable\Repository\RelationshipBuilding
;

class Repository {

    private $modelName;

    function __construct($modelName) {
        $this->modelName = $modelName;
    }

    function getStatement($attachTableName = false, ...$modelNames) {

        if ((gettype($attachTableName) == 'boolean' && $attachTableName)
            || (gettype($attachTableName) == 'string')
            ) {

            if (gettype($attachTableName) == 'string') {
                array_push($modelNames, $attachTableName);
            }

            return $this->mountCollection(...$modelNames);
        }

        return implode(', ', $this->mountMember($this->modelName));
    }

    private function mountMember($modelName, $attachTableName = false, $removePrimaryKeyName = false) {
        InheritanceRelationship
            ::checkIfClassIsA($modelName, 'PReTTable\ModelInterface');

        $model = Reflection::getDeclarationOf($modelName);

        $columnNames = $model->getColumnNames();

        if ($attachTableName) {
            $tableName = $model::getTableName();
        }

        if ($removePrimaryKeyName) {
            $columnNames = array_diff($columnNames,
                [$model->getPrimaryKeyName()]);
        }

        $mountedColumns = [];

        foreach($columnNames as $columnName) {
            $columnName = $columnName;
            array_push($mountedColumns, ($attachTableName ? "$tableName.$columnName" : $columnName));
        }

        return $mountedColumns;
    }

    private function mountCollection(...$modelNames) {
        $mountedColumns = [];

        if (count($modelNames)) {
            $mountedColumns = array_merge($mountedColumns, $this
                ->mountMember($this->modelName, true, true));
        } else {
            $mountedColumns = array_merge($mountedColumns, $this
                ->mountMember($this->modelName, true));
        }

        foreach($modelNames as $modelName) {
            $mountedColumns = array_merge($mountedColumns, $this
                ->mountMember($modelName, true));
        }

        return implode(', ', $mountedColumns);
    }

}
