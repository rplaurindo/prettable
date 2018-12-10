<?php

namespace PReTTable\Repository;

interface WritableModelInterface extends IdentifiableModelInterface {
    
    static function isPrimaryKeySelfIncremental();
    
}
