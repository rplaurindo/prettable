<?php

namespace PReTTable;

interface AbstractAssociativeModel extends AbstractCommonModel {
    
    static function getForeignKeyOf($modelName);
    
}
