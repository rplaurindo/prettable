<?php

namespace PReTTable;

interface IdentifiableModelInterface extends ModelInterface {
    
    static function getPrimaryKeyName();
    
    static function isPrimaryKeySelfIncremental();
    
}
