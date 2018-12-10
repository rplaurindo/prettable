<?php

namespace PReTTable\Repository;

use PReTTable\ModelInterface;

interface IdentifiableModelInterface extends ModelInterface {
    
    static function getPrimaryKeyName();
    
    static function isPrimaryKeySelfIncremental();
    
}
