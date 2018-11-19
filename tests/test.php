<?php

$rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$settingsPath = $rootPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'settings';
require $settingsPath . DIRECTORY_SEPARATOR . 'loadPath.php';

require 'autoload.php';

use PReTTable\AbstractModel;
use PReTTable\AbstractAssociativeModel;
use PReTTable\Model;

use PReTTable\Helpers;

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
        
        $this->model->contains('Model1', 'table2_id');
//         $this->model->contains('Model1', 'table2_id', 'AssociativeModel');
//         $this->model->contains('AssociativeModel', 'table2_id');

        $this->model->contains('Model3', 'table2_column');
        $this->model->contains('Model4', 'table2_column');
        
//         self referencing
//         $this->model->contains('Model2', 'table2_id');
//         $this->model->isContained('Model2', 'table2_id');

//         $this->model->isContained('Model1', 'table1_id');
//         $this->model->isContained('AssociativeModel', 'associative_table_id');
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
    
    function select($tableName) {
        return $this->model->select($tableName);
    }

    function join($modelName, $relatedColumn) {
        return $this->model->join($modelName, $relatedColumn);
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

print_r($model2->select('Model1')->getMap());
// print_r($model2->select('AssociativeModel')->getMap());

// print_r($model2
//     ->select('Model1')
//     ->join('Model3', 'table3_column')
//     ->join('Model4', 'table4_column')
//     ->getMap()
// );

// echo "\n\n";

// print_r($model2->select('Model1'));

// print_r($table2->getAll());

// print_r($table2->getRow(1));
// print_r($table2->getRow('column1', 1));


// $whereClause = new Helpers\WhereClause('Model1', 'Model2');
// print_r($whereClause->mountStatementFor(
//     [
//         'Model1' => [
//             'col1OfModel1' => [
//                 'val1',
//                 'val2'
//             ],
//             'col2OfModel1' => 'val3'
//         ],
//         'Model2' => [
//             'col1OfModel2' => 'val1',
//             'col2OfModel2' => 'val2'
//         ]
//     ]
//     ));

// $whereClause = new Helpers\WhereClause();
// print_r($whereClause->mountStatementFor(
//     [
//         'col1' => [
//             'val1',
//             'val2'
//         ],
//         'col2' => 'val3'
//     ]
//     ));
