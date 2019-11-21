<?php

namespace PreTTable\QueryStatements\Decorators;

use
    PreTTable\Reflection
    , PreTTable\InheritanceRelationship
    , PreTTable\QueryStatements\AbstractComponent
    , PreTTable\QueryStatements\Decorators\Select\AbstractDecorator
;

class Select extends AbstractDecorator {

    private $model;
    
    private $attachTableName;

    function __construct(AbstractComponent $component, $model, $attachTableName = false) {
        parent::__construct($component);
        
        $this->model = $model;
        
        $this->attachTableName = $attachTableName;
        
        $this->_statement = $this->resolveStatement();
    }

    private function resolveStatement() {
        if (gettype($this->model) == 'string') {
            InheritanceRelationship
                ::throwIfClassIsntA($this->model, 'PreTTable\ModelInterface');
            
            $modelDeclaration = Reflection::getDeclarationOf($this->model);
            $this->model = Reflection::getInstanceOf($this->model);
        } else if (gettype($this->model) == 'object') {
            InheritanceRelationship::throwIfClassIsntA(get_class($this->model),
                'PreTTable\ModelInterface');
            $modelDeclaration = Reflection
                ::getDeclarationOf(get_class($this->model));
        }
        
        $columnNames = $this->model->getColumnNames();
        
        if ($this->attachTableName) {
            $tableName = $modelDeclaration::getTableName();
        }
        
        $mountedColumns = [];
        
        foreach($columnNames as $columnName) {
            $columnName = $columnName;
            array_push($mountedColumns, ($this->attachTableName
                ? "$tableName.$columnName AS '{$tableName}.{$columnName}'"
                : $columnName)
            );
        }
        
        return implode(', ', $mountedColumns);
    }

}
