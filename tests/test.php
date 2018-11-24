<?php

$rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$settingsPath = $rootPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'settings';
require $settingsPath . DIRECTORY_SEPARATOR . 'loadPath.php';

require 'autoload.php';

use PReTTable\AbstractModel;
use PReTTable\AbstractAssociativeModel;
use PReTTable\Model;

class Model1 implements AbstractModel {
    
    function __construct() {
        
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

class Model2 implements AbstractModel {
    
    private $model;
    
    function __construct() {
        $this->model = new Model(self::class);
        
        $this->model->containsThrough('Model1', 'AssociativeModel');
//         $this->model->contains('Model1', 'table1_column');
//         $this->model->isContained('Model1', 'table1_id');
        
//         $this->model->contains('AssociativeModel', 'associative_table_column');
//         $this->model->isContained('AssociativeModel', 'associative_table_column');

//         to make join
//         $this->model->contains('Model3', 'table2_column');
//         $this->model->contains('Model4', 'table2_column');

//         $this->model->isContained('Model3', 'table3_id');
//         $this->model->isContained('Model4', 'table4_id');
        
//         self referencing
//         $this->model->contains('Model2', 'table2_id');
//         $this->model->isContained('Model2', 'table2_id');
    }
    
    static function getTableName() {
        return 'table2';
    }
    
    static function getPrimaryKey() {
        return 'ID_table2';
    }
    
    static function getColumns() {
        return [
            'ID_table2' => 'idAlias',
            'column1' => 'column1Alias'
        ];
    }
//     dar a oportunidade de passar um id para c� para montar a clausula where
    function read($tableName, $id='') {
        return $this->model->read($tableName, $id);
    }

    function join($modelName, $relatedColumn) {
        return $this->model->join($modelName, $relatedColumn);
    }
    
    function getAll() {
        return $this->model->getAll();
    }
    
    function getRow($columnName, $value = '') {
        return $this->model->getRow($columnName, $value);
    }
    
    function create($attributes) {
        return $this->model->create($attributes);
    }
    
}

class Model3 implements AbstractModel {
    
    static function getTableName() {
        return 'table3';
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

class Model4 implements AbstractModel {
    
    static function getTableName() {
        return 'table4';
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

class Model5 {
    
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
    
    static function getColumns() {
        return array_values(self::$association);
    }
    
    static function getForeignKeyOf($modelName) {
        return self::$association[$modelName]; 
    }
    
}

$model2 = new Model2();

// print_r($model2->create(
//     [
//         'column1' => 'value1',
//         'column2' => 'value2'
//     ]
// )->getMap());

// print_r($model2->read('Model1')->getMap());

// print_r($model2
//     ->read('Model1')
    
//     ->join('Model3', 'table2_id')
//     ->join('Model4', 'table2_id')
    
// //     ->join('Model3', Model3::getPrimaryKey())
// //     ->join('Model4', Model4::getPrimaryKey())
    
//     ->getMap()
// );

// print_r($model2->read('AssociativeModel')->getMap());

// print_r($model2
//     ->read('AssociativeModel')
    
// //     ->join('Model3', 'table2_id')
// //     ->join('Model4', 'table2_id')
    
//     ->join('Model3', Model3::getPrimaryKey())
//     ->join('Model4', Model4::getPrimaryKey())
    
//     ->getMap()
// );

// echo "\n\n";

// print_r($model2->getAll()->getMap());

// print_r($model2->getRow(1)->getMap());
print_r($model2->getRow('column', 1)->getMap());

use PReTTable\Helpers;

$whereClause = new Helpers\WhereClause('table1', 'table2');
// print_r($whereClause->mount(
//     [
//         'table1' => [
//             'col1OfModel1' => [
//                 'val1',
//                 'val2'
//             ],
//             'col2OfModel1' => 'val3'
//         ],
//         'table2' => [
//             'col1OfModel2' => 'val1',
//             'col2OfModel2' => 'val2'
//         ]
//     ]
// ));

// $whereClause = new Helpers\WhereClause();
// print_r($whereClause->mount(
//     [
//         'col1' => [
//             'val1',
//             'val2'
//         ],
//         'col2' => 'val3'
//     ]
// ));

use PReTTable\SelectStatement;

$selectStatement = new SelectStatement(Model5::class);
// $selectStatement->mount();
