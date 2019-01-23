<?php

namespace PReTTable\QueryStatements;

use
    PReTTable\Reflection,
    PReTTable\Repository\RelationshipBuilding
;

class Select {

    private $modelName;

    function __construct($modelName = null) {
        $this->modelName = $modelName;
    }

    function setModelName($modelName) {
        $this->modelName = $modelName;
    }

    function unsetModelName() {
        $this->modelName = null;
    }

    function getStatement(...$modelNames) {

        if (count($modelNames)) {
            return $this->mountCollection(...$modelNames);
        }

        return implode(', ', $this->mountMember($this->modelName, false));
    }

    private function mountMember($modelName, $attachTableName, $removePrimaryKeyName = false) {
        RelationshipBuilding::checkIfModelIs($modelName, 'PReTTable\ModelInterface');

        $model = Reflection::getDeclarationOf($modelName);
        $columns = $model::getColumns();

        if ($attachTableName) {
            $tableName = RelationshipBuilding::resolveTableName($modelName);
        }

        if ($removePrimaryKeyName) {
            $columns = array_diff($columns, [$model->getPrimaryKeyName()]);
        }

        $mountedColumns = [];

        foreach($columns as $k => $v) {
            if (is_string($k)) {
                $columnName = $k;
                $alias = $v;
                if ($attachTableName) {
                    array_push($mountedColumns, "$tableName.$columnName as $tableName.$alias");
                } else {
                    array_push($mountedColumns, "$columnName as $alias");
                }
            } else {
                $columnName = $v;
                array_push($mountedColumns, ($attachTableName ? "$tableName.$columnName" : $columnName));
            }
        }

        return $mountedColumns;
    }

    private function mountCollection(...$modelNames) {
        $mountedColumns = [];

        if (is_subclass_of($this->modelName,
            'PReTTable\Repository\AssociativeModelInterface')
            ) {
            $associativeModel = Reflection::getDeclarationOf($this->modelName);
            $associatedModelNames = array_intersect(array_keys($associativeModel
                ->getAssociativeKeys()), $modelNames);

            foreach($associatedModelNames as $modelName) {
                $mountedColumns = array_merge($mountedColumns, $this
                    ->mountMember($modelName, true, true));
            }

            $modelNames = array_diff($modelNames, $associatedModelNames);
        } else {
            $mountedColumns = array_merge($mountedColumns, $this
                ->mountMember($this->modelName, true, true));
        }

        foreach($modelNames as $modelName) {
            $mountedColumns = array_merge($mountedColumns, $this
                ->mountMember($modelName, true));
        }

        return implode(', ', $mountedColumns);
    }

}
