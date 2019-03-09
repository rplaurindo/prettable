<?php

namespace PReTTable;

abstract class AbstractPaginableModel extends \AbstractModel {
    
    protected $pagerStrategyContext;
    
    protected $strategyContextIsDefined;

    function __construct($environment = null, array $connectionData) {
        parent::__construct($environment, $connectionData);
        
        $this->pagerStrategyContext = new PaginableStrategyContext();
        $this->strategyContextIsDefined = false;
    }
    
//     a proxy to set strategy context
    protected function setPager(PaginableStrategyInterface $pagerStrategy) {
        echo "\nsetPager\n";
        
        $this->strategyContextIsDefined = true;
        
        echo "\n$this->strategyContextIsDefined\n";
        
        $this->pagerStrategyContext->setStrategy($pagerStrategy);
    }

}
