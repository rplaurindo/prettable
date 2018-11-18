<?php

namespace PReTTable;

use ReflectionClass;

class Reflection {

    function __construct() {
        
    }
    
    static function getDeclarationOf($modelName) {
        $reflection = new ReflectionClass($modelName);
        return $reflection->newInstanceWithoutConstructor();
    }
    
}
