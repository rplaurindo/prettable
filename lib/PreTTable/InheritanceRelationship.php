<?php

namespace PreTTable;

use
    Exception
;

class InheritanceRelationship {

    function __construct() {

    }
    
    static function throwIfClassIsntA($modelName, ...$classList) {
        $count = 0;

        foreach ($classList as $class) {
            if (is_subclass_of($modelName, $class)) {
                $count++;
            }
        }

        if (!$count) {
            $classesAsText = implode(' or ', $classList);
            throw new Exception("The input class must be a $classesAsText");
        }

    }

}
