<?php

$rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$settingsPath = $rootPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'settings';
require $settingsPath . DIRECTORY_SEPARATOR . 'loadPath.php';

require 'autoload.php';

use PReTTable\AbstractModel;
use PReTTable\AbstractAssociativeModel;
use PReTTable\Model;
use PReTTable\GeneralAbstractModel;

class Model1 implements AbstractModel {
    
    function __construct() {
        
    }
    
    static function getTableName() {
        return 'table1';
    }
    
    static function getPrimaryKey() {
        return 'id';
    }
    
    static function getFields() {
        return [
            'id',
            'field1' => 'field1Alias'
        ];
    }
    
}

class Model2 implements AbstractModel {
    
    private $model;
    
    function __construct() {
        $this->model = new Model(self::class);
        
//         $this->model->contains('Model1', 'table2_id');
        $this->model->contains('Model1', 'table2_id', 'AssociativeModel');
        $this->model->contains('Model3', 'table3_field');
        
//         self referencing
//         $this->model->contains('Model2', 'table2_id');
//         $this->model->isContained('Model2', 'table2_id');

//         $this->model->isContained('Model1', 'table1_id');
//         $this->model->isContained('Model1', 'associative_table_id', 'AssociativeModel');
    }
    
    static function getTableName() {
        return 'table2';
    }
    
    static function getPrimaryKey() {
        return 'id';
    }
    
    static function getFields() {
        return [
            'id' => 'idAlias',
            'field1' => 'field1Alias'
        ];
    }
    
    function select($tableName) {
        return $this->model->select($tableName);
    }
    
    function join(...$models) {
        return $this->model->join(...$models);
    }
    
}

class Model3 implements AbstractModel {
    
    static function getTableName() {
        return 'table3';
    }
    
    static function getPrimaryKey() {
        return 'id';
    }
    
    static function getFields() {
        return [
            'id',
            'field1'
        ];
    }
    
}

class Model4 {
    
}

class AssociativeModel implements AbstractModel, AbstractAssociativeModel {
    
    private static $association = [
        'Model1' => 'table1_id',
        'Model2' => 'table2_id'
    ];
    
    static function getTableName() {
        return 'associative_table';
    }
    
    static function getPrimaryKey() {
        return 'id';
    }
    
    static function getFields() {
        return [
            'table1_id',
            'table2_id'
        ];
    }
    
    static function getForeignKeyOf($modelName) {
        return self::$association[$modelName]; 
    }
    
}

$model2 = new Model2();

$model2->join('Model3', 'table2_field');

// print_r($table2->getAll());

// print_r($table2->getRow(1));
// print_r($table2->getRow('field1', 1));

print_r($model2->select('Model1'));
// print_r($model2->select('AssociativeModel'));
