<?php

require 'autoload.php';

use 
    PReTTable\Repository\AbstractModel,
    PReTTable\Repository\AssociativeModelInterface,
    PReTTable\PaginableStrategyInterface,
    PReTTable\Helpers\Pagination
;

class MySQL implements PaginableStrategyInterface {
    
    function getStatement($limit, $pageNumber = 1) {
        $offset = Pagination::calculatesOffset($limit, $pageNumber);
        
        return "
            LIMIT $limit
            OFFSET $offset
        ";
    }
    
}

abstract class ModelBaseTest extends AbstractModel {
    
    function __construct($databaseSchema, $host = null) {
        $data = include 'database.php';
        $host = 'localhost';
        parent::__construct($host, $data);
        $this->establishConnection($databaseSchema);
    }
    
}

class Model1 extends ModelBaseTest {
    
    function __construct() {
        parent::__construct('mydb');
        
        $this->setOrder('id', 'DESC');
        $this->setPager(new MySQL());
        
        $this->containsThrough('Model2', 'AssociativeModel');
        
//         to make join
        $this->contains('Model3', 'table1_id');
    }
    
    static function getTableName() {
        return 'table1';
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
            'table1col'
        ];
    }
    
}

class Model2 extends ModelBaseTest {
    
    function __construct() {
        parent::__construct('mydb');
    }
    
    static function getTableName() {
        return 'table2';
    }
    
    static function getPrimaryKeyName() {
        return 'id';
    }
    
    static function isPrimaryKeySelfIncremental() {
        return true;
    }
    
    static function getColumns() {
        return [
            'id',
            'table2col'
        ];
    }
    
}

class Model3 extends ModelBaseTest {
    
    function __construct() {
        parent::__construct('mydb');
    }
    
    static function getTableName() {
        return 'table3';
    }
    
    static function getPrimaryKeyName() {
        return 'id';
    }
    
    static function isPrimaryKeySelfIncremental() {
        return true;
    }
    
    static function getColumns() {
        return [
            'id',
            'table3col'
        ];
    }
    
}

class AssociativeModel implements AssociativeModelInterface {
    
    function __construct() {
        
    }
    
    static function getTableName() {
        return 'table1_table2';
    }
    
    static function getColumns() {
        return [
            'table1_id',
            'table2_id'
        ];
    }
    
    static function getAssociativeKeys() {
        return [
            'Model1' => 'table1_id',
            'Model2' => 'table2_id'
        ];
    }
    
}


$model1 = new Model1();
// $mode2 = new Model2();

// for ($i = 1; $i <= 10; $i++) {
//     $model1 = $model1->create(['table1col' => "a value $i"]);
// }
// echo $model1->save();

// for ($i = 1; $i <= 10; $i++) {
//     $mode2 = $mode2->create(['table2col' => "a value $i"]);
// }
// echo $mode2->save();

// $model1->setPrimaryKeyValue(1);
// print_r(
//     $model1->createAssociations('Model2',
//         [
//             'table2_id' => 1
//         ],
//         [
//             'table2_id' => 2
//         ]
//     )
//     ->save()
// );

// print_r(
//     $model1
//         ->create(['table1col' => 'a value'])
//         ->createAssociations('Model2',
//             [
//                 'table2_id' => 1
//             ],
//             [
//                 'table2_id' => 2
//             ]
//         )
//         ->save()
// );

// $model1->setPrimaryKeyValue(2);
// print_r($model1->getRow());

// if there isn't a self-incremental primary key
// print_r($model1->getRow('table1col', 'a value 2'));

// print_r($model1->getAll());
// print_r($model1->getAll(2));
// print_r($model1->join('Model3', 'table1_id')->getAll(2));
// print_r($model1->getAll(2, 2));

$model1->setPrimaryKeyValue(2);
print_r($model1->get('Model2'));
// print_r($model1->get('Model2', 2, 3));

// $model1->setPrimaryKeyValue(10);
// echo $model1->update(['table1col' => 'a updated value 2'])
//     ->save()
// ;

// $model1->setPrimaryKeyValue(1);
// print_r( 
//     $model1->updateAssociations('Model2',
//         [
//             'table2_id' => 1
//         ],
//         [
//             'table2_id' => 3
//         ]
//     )
//     ->save()
// );

// $model1->setPrimaryKeyValue(142);
// print_r(
//     $model1->delete()
//     ->save()
// );

// $model1->setPrimaryKeyValue(142);
// print_r(
//     $model1->deleteAssociations('Model2')
//     ->save()
// );
