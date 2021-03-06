<?php

require 'autoload.php';

use
    Models\Repository\PDO\MySQL\AbstractModel,
    PReTTable\AssociativeModelInterface
;

class Model1 extends AbstractModel {

    function __construct() {
        parent::__construct('mydb');

        $this->setOrderBy('id', 'DESC');

        $this->contains('Model2', 'table1_id');
        
        $this->containsThrough('Model3', 'AssociativeModel');
        
        $this->isContained('Model4', 'table4_id');
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

    function getColumnNames() {
        return [
            'id',
            'table1col',
            'table4_id'
        ];
    }
    
//     function readAll($limit = null, $pageNumber = 1) {
//         $this->join('');
//         return parent::readAll($limit, $pageNumber);
//     }

}

class Model2 extends AbstractModel {

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

    function getColumnNames() {
        return [
            'id',
            'table2col',
            'table1_id'
        ];
    }

}

class Model3 extends AbstractModel {

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

    function getColumnNames() {
        return [
            'id',
            'table3col'
        ];
    }

}

class Model4 extends AbstractModel {

    function __construct() {
        parent::__construct('mydb');
    }

    static function getTableName() {
        return 'table4';
    }

    static function getPrimaryKeyName() {
        return 'id';
    }

    static function isPrimaryKeySelfIncremental() {
        return true;
    }

    function getColumnNames() {
        return [
            'id',
            'table4col'
        ];
    }

}

class AssociativeModel implements AssociativeModelInterface {

    function __construct() {

    }

    static function getTableName() {
        return 'table1_table3';
    }

    static function getAssociativeColumnNames() {
        return [
            'Model1' => 'table1_id',
            'Model3' => 'table3_id'
        ];
    }

    function getColumnNames() {
        return [
            'table1_id',
            'table3_id'
        ];
    }

}


$model1 = new Model1();
// $mode2 = new Model2();
$model3 = new Model3();
// $model4 = new Model4();

// for ($i = 1; $i <= 1; $i++) {
//     $model1 = $model1->create(
//         [
//             'table1col' => "a value $i"
//         ]
//     );
// }
// echo $model1->save();

// for ($i = 1; $i <= 10; $i++) {
//     $mode2 = $mode2->create(['table2col' => "a value $i"]);
// }
// echo $mode2->save();

// for ($i = 1; $i <= 2; $i++) {
//     $model3 = $model3->create(
//         [
//             'table3col' => "a value $i",
//             'table1_id' => $i
//         ]
//     );
// }
// echo $model3->save();

// for ($i = 1; $i <= 2; $i++) {
//     $model4 = $model4->create(
//         [
//             'table4col' => "a value $i"
//             , 'table1_id' => 1
//         ]
//     );
// }
// echo $model4->save();


// ASSOCIATIONS

// print_r(
//     $model1
//         ->create(
//             [
//                 'table1col' => 'a value',
// //                 'table4_id' => 1
//             ]
//         )
//         ->createAssociations('Model3',
//             [
//                 'table3_id' => 2
//             ]
//         )
//         ->save()
// );

$model1->setPrimaryKeyValue(1);

// print_r(
//     $model1->createAssociations('Model3',
//         [
//             'table3_id' => 2
//         ]
//     )
//     ->save()
// );


// print_r(
//     $model1->update(
//         [
//             'table4_id' => 2,
//         ]
//     )
//     ->save()
// );

// print_r(
//     $model1->updateAssociations('Model2',
//         [
//             'table2_id' => 3
//         ],
//         [
//             'table2_id' => 4
//         ]
//     )
//     ->save()
// );

// print_r(
//     $model1->delete()
//     ->save())
// ;

// print_r(
//     $model1->deleteAssociations('Model2')
//     ->save())
// ;


// SELECTs

// $model1->setOrderBy('table1.id');

// $model1->join('Model2');

// print_r($model1->read('id', 1));
// print_r($model1->read());

print_r($model1->readAll(2, 2));
// print_r($model1->readAll());

// print_r($model1->readFrom('Model2'));
// print_r($model1->readFrom('Model3'));
// print_r($model1->readFrom('Model4'));

// print_r($model1->readParent('Model4'));
