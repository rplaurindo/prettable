<?php

namespace PReTTable\QueryStatements;

use
    PReTTable\ModelInterface,
    PReTTable\Reflection,
    PReTTable\InheritanceRelationship
;

class Select {

    private $model;

    function __construct(ModelInterface $model) {
        $this->model = $model;
    }

//     function getStatement($attachTableName = false, ...$modelNames) {

//         if ((gettype($attachTableName) == 'boolean' && $attachTableName)
//             || (gettype($attachTableName) == 'string')
//             ) {

//             if (gettype($attachTableName) == 'string') {
//                 array_push($modelNames, $attachTableName);
//             }

//             return $this->mountCollection(...$modelNames);
//         }

//         return implode(', ', $this->mountMember($this->modelName));
//     }

    function getStatement(...$modelNames) {

        if (count($modelNames)) {
            return $this->mountCollection(...$modelNames);
        }

        return implode(', ', $this->mountMember($this->model));
    }

    private function mountMember($model, $attachTableName = false,
        $removePrimaryKeyName = false) {

        if (gettype($model) == 'string') {
            InheritanceRelationship
                ::checkIfClassIsA($model, 'PReTTable\ModelInterface');

            $modelDeclaration = Reflection::getDeclarationOf($model);
            $model = Reflection::getInstanceOf($model);
        } else if (!gettype($model) == 'object'
            || !($model instanceof ModelInterface)) {
            InheritanceRelationship::checkIfClassIsA(get_class($model),
                'PReTTable\ModelInterface');
        }

        $modelDeclaration = Reflection::getDeclarationOf(get_class($model));

        $columnNames = $model->getColumnNames();

        if ($attachTableName) {
            $tableName = $modelDeclaration::getTableName();
        }

        if ($removePrimaryKeyName) {
            $columnNames = array_diff($columnNames,
                [$modelDeclaration::getPrimaryKeyName()]);
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
                ->mountMember($this->model, true, true));
        } else {
            $mountedColumns = array_merge($mountedColumns, $this
                ->mountMember($this->model, true));
        }

        foreach($modelNames as $modelName) {
            $mountedColumns = array_merge($mountedColumns, $this
                ->mountMember($modelName, true));
        }

        return implode(', ', $mountedColumns);
    }

}
