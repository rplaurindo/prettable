<?php

namespace PReTTable\Repository;

use PReTTable\ModelInterface;

interface WritableModelInterface extends ModelInterface {
    
    static function isPrimaryKeySelfIncremental();
    
}
