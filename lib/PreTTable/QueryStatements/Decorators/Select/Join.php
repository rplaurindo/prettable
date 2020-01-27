<?php

namespace PreTTable\QueryStatements\Decorators\Select;

use
    PreTTable\InheritanceRelationship,
    PreTTable\QueryStatements,
    PreTTable\QueryStatements\AbstractComponent,
    PreTTable\Reflection
;

class Join extends QueryStatements\AbstractDecorator {
    
    private $rightTableName;
    
    private $rightColumnName;
    
    private $leftModel;
    
    private $leftColumnName;
    
    private $type;

//     TODO: adds support to make join like WhereClauseStatement
    function __construct(AbstractComponent $component, $rightModelName, $rightColumnName, $leftModelName, $leftColumnName, $type = 'INNER') {
        InheritanceRelationship
            ::throwIfClassIsntA($rightModelName, 'PreTTable\ModelInterface');
        
        parent::__construct($component);
        
        $rightModel = Reflection::getInstanceOf($rightModelName);
        $this->rightTableName = $rightModel->getTableName();
        
        $this->rightColumnName = $rightColumnName;
        
        $leftModel = Reflection::getInstanceOf($leftModelName);
        $this->leftTableName = $leftModel->getTableName();
        
        $this->leftColumnName = $leftColumnName;
        
        $this->type = $type;
        
        $this->_statement = $this->resolveStatement();
    }

    private function resolveStatement() {
        return "$this->type JOIN $this->rightTableName ON $this->rightTableName.$this->rightColumnName = $this->leftTableName.$this->leftColumnName";
    }

}
