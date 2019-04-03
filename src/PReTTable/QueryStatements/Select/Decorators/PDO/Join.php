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
    
    private $rightModelName;
    
    private $rightColumnName;
    
    private $type;

    function __construct(QueryStatements\AbstractComponent $component, AbstractModel $leftModel, $leftColumnName, $rightModelName, $rightColumnName, $type = 'INNER') {
        InheritanceRelationship
            ::checkIfClassIsA($rightModelName, 'PReTTable\ModelInterface');
        
        parent::__construct($component);

        $this->leftModel = $leftModel;
        
        $this->leftColumnName = $leftColumnName;
        
        $this->rightModelName = $rightModelName;
        
        $this->rightColumnName = $rightColumnName;
        
        $this->type = $type;
    }

    function getStatement() {
        $clone = $this->getClone();

        
        
        
        $statement = '';
        
        foreach ($this->model->getJoins() as $type => $modelNames) {
            foreach ($modelNames as $leftModelName => $joins) {
                
                $leftModel = Reflection::getDeclarationOf($leftModelName);
                $leftTableName = $leftModel::getTableName();
                
                foreach ($joins as $joinedModelName => $joinedColumns) {
                    $joinedModel = Reflection::getDeclarationOf($joinedModelName);
                    $joinedTableName = $joinedModel::getTableName();
                    $leftColumnName = $joinedColumns['leftColumnName'];
                    $columnName = $joinedColumns['columnName'];
                    
                    $statement .= "\n\n\t$type JOIN $joinedTableName ON $joinedTableName.$columnName = $leftTableName.$leftColumnName";
                }
            }
            
        }

        return $statement;
    }

}
