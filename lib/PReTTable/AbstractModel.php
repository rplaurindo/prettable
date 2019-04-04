<?php

namespace PReTTable;

use
    ArrayObject,
    Exception,
//     PReTTable\QueryStatements\AbstractComponent,
    PReTTable\QueryStatements\Component,
    PReTTable\QueryStatements\Decorators\Select,
    PReTTable\QueryStatements\Decorators\Select\Join
;

abstract class AbstractModel extends AbstractModelBase
    implements
        \PReTTable\IdentifiableModelInterface
{

    protected $primaryKeyValue;

    protected $orderBy;

    protected $orderOfOrderBy;

    protected $name;
    
    protected $selectDecorator;
    
    protected $joinsDecorator;
    
    private $involvedTableNames;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->name = get_class($this);
        
        $this->involvedModelNames = new ArrayObject();
        $this->involvedTableNames = new ArrayObject();
    }

    function setPrimaryKeyValue($value) {
        $this->primaryKeyValue = $value;
    }

    function setOrderBy($columnName, $order = '') {
        $this->orderBy = $columnName;
        $this->orderOfOrderBy = $order;
    }

    function join($modelName, $columnName, $leftColumnName, $type = 'INNER', $leftModelName = null) {
        $clone = $this->getClone();
        
        if (isset($leftModelName)) {
            InheritanceRelationship
                ::checkIfClassIsA($leftModelName, 'PReTTable\ModelInterface');
        } else {
            $leftModelName = $clone->name;
        }
        
        $clone->addsInvolvedTable($modelName);
        
        if (!isset($clone->selectDecorator)) {
            $clone->selectDecorator = new Component('SELECT ');
        }
        
        $clone->selectDecorator = new Select($clone->selectDecorator, $modelName, true);
        
        if (!isset($clone->joinsDecorator)) {
            $clone->joinsDecorator = new Component();
        }
        
        $clone->joinsDecorator = new Join($clone->joinsDecorator, $clone, $leftColumnName, $modelName, $columnName);
        
        return $clone;
    }
    
    protected function addsInvolvedTable($modelName) {
        InheritanceRelationship
            ::checkIfClassIsA($modelName, 'PReTTable\ModelInterface');
        
        $model = Reflection::getDeclarationOf($modelName);
        $tableName = $model::getTableName();
        if (!array_search($tableName, $this->involvedTableNames->getArrayCopy())) {
            $this->involvedTableNames->append($tableName);
        }
        
    }
    
    protected function getOrderByStatement() {

        if (isset($this->orderBy)) {
            if (count($this->getInvolvedTableNames())) {
                $explodedOrderByStatement = explode('.', $this->orderBy);
                
                if (count($explodedOrderByStatement) != 2
                    || !in_array($explodedOrderByStatement[0],
                        $this->getInvolvedTableNames())
                    ) {
                        throw new Exception("The defined column of \"ORDER BY\" statement must be fully qualified containing " . implode(' or ', $this->getInvolvedTableNames()));
                    }
            }
            
            return "\n\n\tORDER BY $this->orderBy $this->orderOfOrderBy";
        }
        
        return null;
    }
    
    private function getInvolvedTableNames() {
        return $this->involvedTableNames->getArrayCopy();
    }

}
