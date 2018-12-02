<?php

require 'autoload.php';

use PReTTable\IdentifiableModelInterface;
// use PReTTable\AssociativeModelInterface;
use PReTTable\AbstractModel;

class ModelBaseTest extends AbstractModel {
    
    function __construct($database, $host = null) {
        $data = include 'database.php';
        $host = 'localhost';
        parent::__construct($host, $data);
        $this->establishConnection($database);
    }
    
}

class Model1 extends ModelBaseTest implements IdentifiableModelInterface {
    
    function __construct() {
        parent::__construct('test_schema');
        
//         definir relacionamentos aqui
    }
    
    static function getTableName() {
        return 'table_1';
    }
    
    static function getPrimaryKey() {
        return 'id';
    }
    
    static function getColumns() {
        return [
            'id',
            'column1'
        ];
    }
    
}

$model = new Model1();
// $model->create(['column1' => 'a value']);
$model->update(11, ['column1' => 'a updated value']);
