<?php

namespace PReTTable\Repository;

use PReTTable\ModelInterface;

interface AssociativeModelInterface extends ModelInterface {
    
    static function getAssociativeKeys();
    
}
