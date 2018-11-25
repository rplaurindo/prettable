<?php

namespace PReTTable;

use ReflectionClass;

class Reflection {
    
    static function getDeclarationOf($modelName) {
        $reflection = new ReflectionClass($modelName);
        return $reflection->newInstanceWithoutConstructor();
    }
    
}
