<?php

$rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$settingsPath = $rootPath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'settings';
require $settingsPath . DIRECTORY_SEPARATOR . 'loadPath.php';

require 'autoload.php';

use PReTTable\AbstractTable;
use PReTTable\AbstractAssociativeTable;
use PReTTable\Model;

class Table1 implements AbstractTable {
    
    private $table;
    
    function __construct() {
        parent::__construct();
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

class Table2 implements AbstractTable {
    
    private $model;
    
    function __construct() {
        $this->model = new Model(self::class);
        
//         $this->model->contains('Table1', 'table2_id');
        $this->model->contains('Table1', 'table2_id', 'AssociativeTable');

//         $this->model->isContained('Table1', 'table1_id');
//         $this->model->isContained('Table1', 'table1_id', 'AssociativeTable');
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
    
}

class AssociativeTable implements AbstractAssociativeTable {
    
    private static $foreignKeys = [
        'Table1' => 'table1_id',
        'Table2' => 'table2_id'
    ];
    
    static function getForeignKeyOf($tableName) {
        return self::$foreignKeys[$tableName]; 
    }
    
}

$table2 = new Table2();

// print_r($table2->getAll());

// print_r($table2->getRow(1));
// print_r($table2->getRow('field1', 1));

print_r($table2->select('Table1'));
