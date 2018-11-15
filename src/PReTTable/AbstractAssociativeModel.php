<?php

namespace PReTTable;

interface AbstractAssociativeModel extends GeneralAbstractModel {
    
    static function getForeignKeyOf($modelName);
    
}
