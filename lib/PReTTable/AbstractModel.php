<?php

namespace PReTTable;

use
    ArrayObject,
    Exception,
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
        
        $this->involvedTableNames = new ArrayObject();
        $this->involvedTableNames->append($this->getTableName());
    }

    function setPrimaryKeyValue($value) {
        $this->primaryKeyValue = $value;
    }

    function setOrderBy($columnName, $order = '') {
        $this->orderBy = $columnName;
        $this->orderOfOrderBy = $order;
    }

    function join($modelName, $columnName, $leftColumnName, $type = 'INNER', $leftModelName = null) {
        if (isset($leftModelName)) {
            InheritanceRelationship
                ::throwIfClassIsntA($leftModelName, 'PReTTable\ModelInterface');
        } else {
            $leftModelName = $this->name;
        }
        
        $this->addsInvolvedTable($modelName);
        
        if (!isset($this->selectDecorator)) {
            $this->selectDecorator = new Component('SELECT ');
        }
        
        $this->selectDecorator = new Select($this->selectDecorator, $modelName, true);
        
        if (!isset($this->joinsDecorator)) {
            $this->joinsDecorator = new Component();
        }
        
        $this->joinsDecorator = new Join($this->joinsDecorator, $this, $leftColumnName, $modelName, $columnName);
    }
    
    protected function addsInvolvedTable($modelName) {
        InheritanceRelationship
            ::throwIfClassIsntA($modelName, 'PReTTable\ModelInterface');
        
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
