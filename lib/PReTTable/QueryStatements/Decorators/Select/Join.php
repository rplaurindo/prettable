<?php

namespace PReTTable\QueryStatements\Decorators\Select;

use
    PReTTable\InheritanceRelationship,
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\AbstractComponent,
    PReTTable\Reflection
;

class Join extends QueryStatements\AbstractDecorator {
    
    private $rightTableName;
    
    private $rightColumnName;
    
    private $leftModel;
    
    private $leftColumnName;
    
    private $type;

    function __construct(AbstractComponent $component, $rightModelName, $rightColumnName, $leftModelName, $leftColumnName, $type = 'INNER') {
        InheritanceRelationship
            ::throwIfClassIsntA($rightModelName, 'PReTTable\ModelInterface');
        
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
