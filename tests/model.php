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
    
    static function getPrimaryKeyName() {
        return 'id';
    }
    
    static function isPrimaryKeySelfIncremental() {
        return true;
//         return false;
    }
    
    static function getColumns() {
        return [
            'id',
            'column1'
        ];
    }
    
}

$model = new Model1();
// print_r($model->getRow(20));
// print_r($model->getRow('column1', 'value2'));

// print_r($model->create(['column1' => 'a value'])->commit());

// $model->createAssociation(['column1' => 'a value 1'], ['column1' => 'a value 2']);

// print_r($model->update(22, ['column1' => 'a updated value'])->commit());

echo $model->delete('id', 28);
