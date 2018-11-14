<?php

namespace PReTTable;

interface AbstractModel extends AbstractCommonModel {
    
    static function getPrimaryKey();
    
    static function getFields();
    
}
