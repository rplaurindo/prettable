<?php

namespace PreTTable;

use
    ArrayObject
    , Exception
    , PreTTable\QueryStatements\Component
    , PreTTable\QueryStatements\Decorators\Select
    , PreTTable\QueryStatements\Decorators\Select\Join
;
use PreTTable\QueryStatements\Decorators\ColumnSelect;

abstract class AbstractModel extends AbstractModelBase
    implements
        IdentifiableModelInterface
{

    protected $primaryKeyValue;

    protected $orderBy;

    protected $orderOfOrderBy;

    protected $name;
    
    protected $columnSelectDecorator;
    
    protected $joinsDecorator;
    
    private $involvedTableNames;

    function __construct(array $connectionData, $environment = null) {
        parent::__construct($connectionData, $environment);

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
    
    /**
     * @param string $modelName
     * @param string $columnName
     * @param string $leftColumnName
     * @param string $type [optional] 'INNER' is the default value
     * @param string $leftModelName [optional]
     * @return void
     */
    function join() {
        $modelName = func_get_arg(0);
        $columnName = func_get_arg(1);
        $leftColumnName = func_get_arg(2);
        $type = 'INNER';
        $leftModelName = null;
        
        if (count(func_get_args()) > 3) {
            $type = func_get_arg(3);
            if (count(func_get_args()) > 4) {
                $leftModelName = func_get_arg(4);
            }
        }
        
        if (isset($leftModelName)) {
            InheritanceRelationship
                ::throwIfClassIsntA($leftModelName, 'PreTTable\ModelInterface');
            
            $this->addsInvolvedTable($leftModelName);
        } else {
            $leftModelName = $this->name;
        }
        
        $this->addsInvolvedTable($modelName);
        
        if (!isset($this->columnSelectDecorator)) {
            $component = new Component("SELECT ");
        } else {
            $component = $this->columnSelectDecorator;
        }
        
        $this->columnSelectDecorator = new ColumnSelect($component, $modelName, true);
        
        if (!isset($this->joinsDecorator)) {
            $this->joinsDecorator = new Component();
        }
        
        $this->joinsDecorator = new Join($this->joinsDecorator, $modelName, $columnName, $leftModelName, $leftColumnName, $type);
    }
    
    protected function addsInvolvedTable($modelName) {
        InheritanceRelationship
            ::throwIfClassIsntA($modelName, 'PreTTable\ModelInterface');
        
        $model = Reflection::getDeclarationOf($modelName);
        $tableName = $model::getTableName();
        if (!array_search($tableName, $this->involvedTableNames->getArrayCopy())) {
            $this->involvedTableNames->append($tableName);
        }
        
    }
    
    protected function getOrderByStatement() {

        if (isset($this->orderBy)) {
            if (count($this->getInvolvedTableNames())
                && count($this->getInvolvedTableNames()) > 1
                ) {
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
