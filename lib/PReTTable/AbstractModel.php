<?php

namespace PReTTable;

abstract class AbstractModel extends AbstractModelBase
    implements
        \PReTTable\IdentifiableModelInterface
{

    protected $name;

    protected $primaryKeyValue;

    protected $orderBy;

    protected $orderOfOrderBy;

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);

        $this->name = get_class($this);
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

}
