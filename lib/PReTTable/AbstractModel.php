<?php

namespace PReTTable;

use
    ArrayObject
;

abstract class AbstractModel extends AbstractModelBase
    implements
        \PReTTable\IdentifiableModelInterface
{

    protected $primaryKeyValue;

    protected $orderBy;

    protected $orderOfOrderBy;

    protected $name;

    protected $joins;
    
    private $involvedModelNames;
    
    private $involvedTableNames;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->name = get_class($this);
        
        $this->involvedModelNames = new ArrayObject();
        $this->involvedTableNames = new ArrayObject();

        $this->joins = new ArrayObject();
    }

//     function getName() {
//         return $this->name;
//     }

    function setPrimaryKeyValue($value) {
        $this->primaryKeyValue = $value;
    }

    function setOrderBy($columnName, $order = '') {
        $clone = $this->getClone();

        $clone->orderBy = $columnName;
        $clone->orderOfOrderBy = $order;

        return $clone;
    }

    function join($modelName, $columnName, $leftColumnName, $type = 'INNER', $leftModelName = null) {
        InheritanceRelationship
            ::checkIfClassIsA($modelName, 'PReTTable\ModelInterface');

        $clone = $this->getClone();
        
        if (isset($leftModelName)) {
            InheritanceRelationship
                ::checkIfClassIsA($modelName, 'PReTTable\ModelInterface');
        } else {
            $leftModelName = $clone->name;
        }

        $clone->addsInvolvedModel($modelName);

        $join = [
            'leftColumnName' => $leftColumnName,
            'columnName' => $columnName
        ];

        if ($clone->joins->offsetExists($type)) {
            $currentJoin = $clone->joins->offsetGet($type);

            if (!array_key_exists($leftModelName, $currentJoin)) {
                $currentJoin[$leftModelName] = [];
            }
        } else {
            $currentJoin = [];
            $currentJoin[$leftModelName] = [];
        }
        
        $currentJoin[$leftModelName][$modelName] = $join;

        $clone->joins->offsetSet($type, $currentJoin);

        return $clone;
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
    
    protected function mountJoinsStatement() {
        $statement = '';
        
        print_r($this->joins);
        
//         foreach ($this->joins as $type => $join) {
//             $joinedTables = array_keys($join);
            
//             foreach ($joinedTables as $joinedTableName) {
//                 $joinedColumns = $join[$joinedTableName];
                
//                 $columnName = $joinedColumns['columnName'];
//                 $leftTableColumnName = $joinedColumns['leftTableColumnName'];
                
//                 $leftTableName = $this->getTableName();
                
//                 $statement .= "$type JOIN $joinedTableName ON $joinedTableName.$columnName = $leftTableName.$leftTableColumnName\n";
//             }
            
//         }
        
//         return $statement;
    }
    
    protected function getInvolvedModelNames() {
        return $this->involvedModelNames->getArrayCopy();
    }

    protected function getInvolvedTableNames() {
        return $this->involvedTableNames->getArrayCopy();
    }

}
