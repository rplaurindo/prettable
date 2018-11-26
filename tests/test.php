<?php

$rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$settingsPath = $rootPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'settings';
require $settingsPath . DIRECTORY_SEPARATOR . 'loadPath.php';

require 'autoload.php';

use PReTTable\AbstractModel;
use PReTTable\AbstractAssociativeModel;
use PReTTable\Query;

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
    
    private $query;
    
    function __construct() {
        $this->query = new Query(self::class);
        
//         $this->query->containsThrough('Model1', 'AssociativeModel');
        $this->query->contains('Model1', 'table2_id');
//         $this->query->isContained('Model1', 'table1_id');
        
//         $this->query->contains('AssociativeModel', 'associative_table_column');
//         $this->query->isContained('AssociativeModel', 'associative_table_column');

//         to make join
//         $this->query->contains('Model3', 'table2_column');
//         $this->query->contains('Model4', 'table2_column');

//         $this->query->isContained('Model3', 'table3_id');
//         $this->query->isContained('Model4', 'table4_id');
        
//         self referencing
//         $this->query->contains('Model2', 'table2_id');
//         $this->query->isContained('Model2', 'table2_id');
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
    
    function create(array $attributes) {
        return $this->query->insert($attributes);
    }
    
//     dar a oportunidade de passar um id para cá para montar a clausula where
    function read($tableName, $id='') {
        return $this->query->select($tableName, $id);
    }
    
    function update($id, array $attributes) {
        return $this->query->update($id, $attributes);
    }
    
    function delete($columnName, ...$values) {
        return $this->query->delete($columnName, ...$values);
    }

    function join($modelName, $relatedColumn) {
        return $this->query->join($modelName, $relatedColumn);
    }
    
    function getAll() {
        return $this->query->getAll();
    }
    
    function getRow($columnName, $value = '') {
        return $this->query->getRow($columnName, $value);
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

// print_r($model2->update(1,
//     [
//         'column1' => 'value1',
//         'column2' => 'value2'
//     ]
// )->getMap());

print_r($model2->delete('model2Column', 1, 2)->getMap());

// print_r($model2->read('Model1')->getMap());
// print_r($model2->read('Model1', 1)->getMap());

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
// print_r($model2->getRow('column', 1)->getMap());

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
