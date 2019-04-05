<?php

namespace PReTTable\QueryStatements\Decorators;

use
    PReTTable\Reflection,
    PReTTable\InheritanceRelationship,
    PReTTable\QueryStatements\AbstractComponent,
    PReTTable\QueryStatements\Decorators\Select\AbstractDecorator
;

class Select extends AbstractDecorator {

    private $model;
    
    private $attachTableName;
    
    private $removePrimaryKeyName;

    function __construct(AbstractComponent $component, $model, $attachTableName = false, $removePrimaryKeyName = false) {
        parent::__construct($component);
        
        $this->model = $model;
        
        $this->attachTableName = $attachTableName;
        
        $this->removePrimaryKeyName = $removePrimaryKeyName;
        
        $this->_statement = $this->resolveStatement();
    }

    private function resolveStatement() {
        if (gettype($this->model) == 'string') {
            InheritanceRelationship
                ::checkIfClassIsA($this->model, 'PReTTable\ModelInterface');
            
            $modelDeclaration = Reflection::getDeclarationOf($this->model);
            $this->model = Reflection::getInstanceOf($this->model);
        } else if (gettype($this->model) == 'object') {
            InheritanceRelationship::checkIfClassIsA(get_class($this->model),
                'PReTTable\ModelInterface');
            $modelDeclaration = Reflection
                ::getDeclarationOf(get_class($this->model));
        }
        
        $columnNames = $this->model->getColumnNames();
        
        if ($this->attachTableName) {
            $tableName = $modelDeclaration::getTableName();
        }
        
        if ($this->removePrimaryKeyName) {
            $columnNames = array_diff($columnNames,
                [$modelDeclaration::getPrimaryKeyName()]);
        }
        
        $mountedColumns = [];
        
        foreach($columnNames as $columnName) {
            $columnName = $columnName;
            array_push($mountedColumns, ($this->attachTableName
                ? "$tableName.$columnName"
                : $columnName)
            );
        }
        
        return implode(', ', $mountedColumns);
    }

}
