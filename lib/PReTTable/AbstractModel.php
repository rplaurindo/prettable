<?php

namespace PReTTable;

use
    ArrayObject,
    Exception
;

abstract class AbstractModel extends AbstractModelBase
    implements
        \PReTTable\IdentifiableModelInterface
{

    protected $primaryKeyValue;

    protected $orderBy;

    protected $orderOfOrderBy;

    protected $name;

    private $joins;
    
    private $involvedModelNames;
    
    private $involvedTableNames;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->name = get_class($this);
        
        $this->involvedModelNames = new ArrayObject();
        $this->involvedTableNames = new ArrayObject();

        $this->joins = new ArrayObject();
    }

    function setPrimaryKeyValue($value) {
        $this->primaryKeyValue = $value;
    }

    function setOrderBy($columnName, $order = '') {
        $this->orderBy = $columnName;
        $this->orderOfOrderBy = $order;
    }

    function join($modelName, $columnName, $leftColumnName, $type = 'INNER', $leftModelName = null) {
        InheritanceRelationship
            ::checkIfClassIsA($modelName, 'PReTTable\ModelInterface');

        $clone = $this->getClone();
        
        if (isset($leftModelName)) {
            InheritanceRelationship
                ::checkIfClassIsA($leftModelName, 'PReTTable\ModelInterface');
        } else {
            $leftModelName = $clone->name;
        }

        $clone->addsInvolvedModel($modelName);

        $joinedColumns = [
            'leftColumnName' => $leftColumnName,
            'columnName' => $columnName
        ];

        if ($clone->joins->offsetExists($type)) {
            $join = $clone->joins->offsetGet($type);

            if (!array_key_exists($leftModelName, $join)) {
                $join[$leftModelName] = [];
            }
        } else {
            $join = [];
            $join[$leftModelName] = [];
        }
        
        $join[$leftModelName][$modelName] = $joinedColumns;

        $clone->joins->offsetSet($type, $join);

        return $clone;
    }
    
    protected function mountJoinsStatement() {
        $statement = '';
        
        foreach ($this->joins as $type => $modelNames) {
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
    
    private function getInvolvedTableNames() {
        return $this->involvedTableNames->getArrayCopy();
    }

}
