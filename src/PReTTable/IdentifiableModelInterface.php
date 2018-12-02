<?php

namespace PReTTable;

interface IdentifiableModelInterface extends ModelInterface {
    
    static function getPrimaryKey();
    
    static function isPrimaryKeyAutoIncrement();
    
}
