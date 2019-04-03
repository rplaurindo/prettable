<?php

namespace PReTTable;

use
    ArrayObject,
    Exception,
    PReTTable\QueryStatements\Select\Decorators\PDO\Join,
    PReTTable\QueryStatements\SelectComponent
;

abstract class AbstractModel extends AbstractModelBase
    implements
        \PReTTable\IdentifiableModelInterface
{

    protected $primaryKeyValue;

    protected $orderBy;

    protected $orderOfOrderBy;

    protected $name;
    
    protected $selectComponent;
    
    private $involvedModelNames;
    
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
        
        $clone->addsInvolvedModel($modelName);

        $clone->checkIfThereIsSelectComponent();
        
        $clone->selectComponent = new Join($clone->selectComponent, $clone, $leftColumnName, $modelName, $columnName);
        
        return $clone->selectComponent;
    }
    
    protected function addsInvolvedModel($modelName) {
        InheritanceRelationship
            ::checkIfClassIsA($modelName, 'PReTTable\ModelInterface');
        
        if (!array_search($modelName, $this->involvedModelNames->getArrayCopy())) {
            $this->involvedModelNames->append($modelName);
            $model = Reflection::getDeclarationOf($modelName);
            $this->involvedTableNames->append($model::getTableName());
        }
        
    }
    
    protected function getInvolvedModelNames() {
        return $this->involvedModelNames->getArrayCopy();
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
    
    protected function checkIfThereIsSelectComponent() {
        if (!isset($this->selectComponent)
            || gettype($this->selectComponent) != 'object'
            || !($this->selectComponent instanceof SelectComponent)) {
                throw new Exception('A basic SELECT must be set to join. You must instantiate PReTTable\QueryStatements\SelectComponent\SelectComponent.');
            }
    }
    
    private function getInvolvedTableNames() {
        return $this->involvedTableNames->getArrayCopy();
    }

}
