<?php

namespace PreTTable;

use
    PreTTable\Helpers\StringEncoding
;


abstract class AbstractModelBase {

    protected $stringEncoder;
    
    protected $connectionData;

    function __construct(array $connectionData, $environment = null) {
        $this->stringEncoder = new StringEncoding();
        
        $this->resolveConnectionDataEnvironment($connectionData, $environment);
    }
    
//     talvez isso possa ser limado
    protected abstract function establishConnection($schemaName);

    // to comply the Prototype pattern
    protected function getClone() {
        return clone $this;
    }
    
    private function resolveConnectionDataEnvironment($connectionData, $environment = null) {
        if (isset($environment)) {
            $connectionData = $connectionData[$environment];
        }
        
        $this->connectionData = $connectionData;
    }

}
