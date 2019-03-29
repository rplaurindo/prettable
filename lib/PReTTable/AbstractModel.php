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

    function __construct(array $connectionData) {
        parent::__construct($connectionData);

        $this->name = get_class($this);

        $this->involvedModelNames = new ArrayObject();
        $this->involvedTableNames = new ArrayObject();
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

    protected function getInvolvedTableNames() {
        return $this->involvedTableNames->getArrayCopy();
    }

}
