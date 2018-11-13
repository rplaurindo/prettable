<?php

namespace PReTTable;

interface AbstractTable {
    
    static function getPrimaryKey();
    
    static function getFields();
    
}
