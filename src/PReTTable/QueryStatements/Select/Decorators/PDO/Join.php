<?php

namespace PReTTable\QueryStatements\Select\Decorators\PDO;

use
    PReTTable\AbstractModel,
    PReTTable\InheritanceRelationship,
    PReTTable\QueryStatements,
    PReTTable\QueryStatements\Select\PDO\AbstractDecorator,
    PReTTable\Reflection
;

class Join extends AbstractDecorator {
    
    private $leftModel;
    
    private $leftColumnName;
    
    private $rightTableName;
    
    private $rightColumnName;
    
    private $type;

    function __construct(QueryStatements\AbstractComponent $component, AbstractModel $leftModel, $leftColumnName, $rightModelName, $rightColumnName, $type = 'INNER') {
        InheritanceRelationship
            ::checkIfClassIsA($rightModelName, 'PReTTable\ModelInterface');
        
        parent::__construct($component);

        $this->leftModel = $leftModel;
        
        $this->leftColumnName = $leftColumnName;
        
        $rightModel = Reflection::getInstanceOf($rightModelName);
        $this->rightTableName = $rightModel->getTableName();
        
        $this->rightColumnName = $rightColumnName;
        
        $this->type = $type;
    }

    function getStatement() {
        return "\n\n\t$this->type JOIN $this->rightTableName ON $this->rightTableName.$this->rightColumnName = {$this->leftModel->getTableName()}.$this->leftColumnName";
    }

}
