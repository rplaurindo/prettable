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

    private $name;

    private $involvedModelNames;

    private $involvedTableNames;

    private $joins;

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->name = get_class($this);

        $this->involvedModelNames = new ArrayObject();
        $this->involvedTableNames = new ArrayObject();

        $this->joins = new ArrayObject();
    }

    function getName() {
        return $this->name;
    }

    function setPrimaryKeyValue($value) {
        $this->primaryKeyValue = $value;
    }

    function setOrderBy($columnName, $order = '') {
        $clone = $this->getClone();

        $clone->orderBy = $columnName;
        $clone->orderOfOrderBy = $order;

        return $clone;
    }

    function addsInvolvedModel($modelName) {
        InheritanceRelationship
            ::checkIfClassIsA($modelName, 'PReTTable\ModelInterface');

        $this->involvedModelNames->append($modelName);
        $model = Reflection::getDeclarationOf($modelName);
        $this->involvedTableNames->append($model::getTableName());
    }

    function getInvolvedModelNames() {
        return $this->involvedModelNames->getArrayCopy();
    }

    function join($modelName, $columnName, $leftTableColumnName, $type = 'INNER') {
        InheritanceRelationship::checkIfClassIsA($modelName,
            'PReTTable\ModelInterface');

        $clone = $this->getClone();

        $clone->addsInvolvedModel($modelName);

        $model = Reflection::getDeclarationOf($modelName);
        $tableName = $model::getTableName();

        $joinedColumns = [
            'columnName' => $columnName,
            'leftTableColumnName' => $leftTableColumnName
        ];

        if ($clone->joins->offsetExists($type)) {
            $join = $clone->joins->offsetGet($type);

            if (!array_key_exists($tableName, $join)) {
                $join[$tableName] = $joinedColumns;
            }
        } else {
            $join = [];
            $join[$tableName] = $joinedColumns;
        }

        $clone->joins->offsetSet($type, $join);

        return clone $clone;
    }

    function getJoins() {
        return $this->joins->getArrayCopy();
    }

    protected function getInvolvedTableNames() {
        return $this->involvedTableNames->getArrayCopy();
    }

}
