<?php

namespace PReTTable\Repository;

use PReTTable\ModelInterface;

// ver se tem de herdar de Identifiable
interface WritableModelInterface extends ModelInterface {
    
    static function isPrimaryKeySelfIncremental();
    
}
