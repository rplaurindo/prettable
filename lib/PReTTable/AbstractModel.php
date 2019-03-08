<?php

namespace PReTTable;

use
    Exception,
    PReTTable,
    PReTTable\PaginableStrategyContext
;

abstract class AbstractModel
    implements
        PReTTable\IdentifiableModelInterface
{

    protected $connection;

    protected $modelName;
    
    protected $primaryKeyValue;
    
    protected $orderBy;
    
    protected $orderOfOrderBy;
    
    protected $connectionContext;
    
    protected $environment;
    
    protected $connectionData;
    
    protected $pagerStrategyContext;
    
    protected $strategyContextIsDefined;

    function __construct($environment = null, array $connectionData) {
        if (gettype($environment) == 'array') {
            $connectionData = $environment;
            $environment = null;
        }
        
        $this->environment = $environment;
        $this->connectionData = $connectionData;
        
        $this->modelName = get_class($this);
        
        $this->pagerStrategyContext = new PaginableStrategyContext();
        $this->strategyContextIsDefined = false;
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
    
    //     a proxy to set strategy context
    function setPager(PaginableStrategyInterface $pagerStrategy) {
        $this->strategyContextIsDefined = true;
        
        $this->pagerStrategyContext->setStrategy($pagerStrategy);
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
