<?php

namespace PReTTable\QueryStatements\Decorators\Select;

use
    PReTTable\AbstractModel,
    PReTTable\InheritanceRelationship,
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\AbstractComponent,
    PReTTable\Reflection
;

class Join extends QueryStatements\AbstractDecorator {
    
    private $leftModel;
    
    private $leftColumnName;
    
    private $rightTableName;
    
    private $rightColumnName;
    
    private $type;

    function __construct(AbstractComponent $component, AbstractModel $leftModel, $leftColumnName, $rightModelName, $rightColumnName, $type = 'INNER') {
        InheritanceRelationship
            ::checkIfClassIsA($rightModelName, 'PReTTable\ModelInterface');
        
        parent::__construct($component);

        $this->leftModel = $leftModel;
        
        $this->leftColumnName = $leftColumnName;
        
        $rightModel = Reflection::getInstanceOf($rightModelName);
        $this->rightTableName = $rightModel->getTableName();
        
        $this->rightColumnName = $rightColumnName;
        
        $this->type = $type;
        
        $this->_statement = $this->resolveStatement();
    }

    private function resolveStatement() {
        return "$this->type JOIN $this->rightTableName ON $this->rightTableName.$this->rightColumnName = {$this->leftModel->getTableName()}.$this->leftColumnName";
    }

}
