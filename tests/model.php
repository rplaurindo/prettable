<?php

use PReTTable\IdentifiableModelInterface;
use PReTTable\AssociativeModelInterface;
use PReTTable\QueryMap;
use PReTTable\AbstractModel;

class AbstractModelTest extends AbstractModel {
    
    function __construct($database, $host = null) {
//         pegar os dados aqui com include
        
        parent::__construct($host, $data);
        $this->establishConnection($database);
    }
    
}

class Model1 extends AbstractModelTest implements IdentifiableModelInterface {
    
    function __construct() {
        parent::__construct($database);
    }
    
    static function getTableName() {
        return 'table1';
    }
    
    static function getPrimaryKey() {
        return 'ID_table1';
    }
    
    static function getColumns() {
        return [
            'ID_table1',
            'column2' => 'column2Alias'
        ];
    }
    
}