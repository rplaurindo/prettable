<?php

namespace PReTTable;

interface AssociativeModelInterface extends ModelInterface {
    
    static function getForeignKeyNameOf($modelName);
    
}
