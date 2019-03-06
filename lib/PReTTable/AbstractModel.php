<?php

namespace PReTTable;

use
    Exception
;

abstract class AbstractModel
    implements
        IdentifiableModelInterface,
        WritableModelInterface
{   

    protected $connection;

    protected $modelName;
    
    protected $primaryKeyValue;
    
    protected $orderBy;
    
    protected $orderOfOrderBy;
    
    protected $connectionContext;
    
    protected $environment;
    
    protected $connectionData;

    function __construct($environment = null, array $connectionData) {
        if (gettype($environment) == 'array') {
            $connectionData = $environment;
            $environment = null;
        }
        
        $this->environment = $environment;
        $this->connectionData = $connectionData;
        $this->modelName = get_class($this);
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

    protected function establishConnection($schemaName, $host = null) {
        if (!isset($schemaName)) {
            throw new Exception('A database schema should be passed.');
        }

        $this->connection = $this->connectionContext
            ->establishConnection($schemaName, $host);
    }

//     to comply the Prototype pattern
    protected function getClone() {
        return clone $this;
    }

}
