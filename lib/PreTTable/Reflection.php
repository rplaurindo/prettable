<?php

namespace PreTTable;

use ReflectionClass;

class Reflection {

    static function getDeclarationOf($modelName) {
        $reflection = new ReflectionClass($modelName);
        return $reflection->newInstanceWithoutConstructor();
    }

    static function getInstanceOf($modelName) {
        $reflection = new ReflectionClass($modelName);
        return $reflection->newInstance();
    }

}
