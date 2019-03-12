<?php

require 'autoload.php';

use
    Models\MySQL\AbstractModel,
    PReTTable\AssociativeModelInterface
;

class Model1 extends AbstractModel {

    function __construct() {
        parent::__construct('mydb');

        $ordered = $this->setOrderBy('id', 'DESC');

        $ordered->containsThrough('Model2', 'AssociativeModel');

        $ordered->contains('Model4', 'table1_id');
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

    static function getColumnNames() {
        return [
            'id',
            'table1col'
        ];
    }

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

    static function getColumnNames() {
        return [
            'id',
            'table2col'
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

    static function getColumnNames() {
        return [
            'id',
            'table3col',
            'table1_id'
        ];
    }

}

class Model4 extends AbstractModel {

    function __construct() {
        parent::__construct('mydb');

        $this->isContained('Model1', 'table1_id');
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

    static function getColumnNames() {
        return [
            'id',
            'table4col',
            'table1_id'
        ];
    }

}

class AssociativeModel implements AssociativeModelInterface {

    function __construct() {

    }

    static function getTableName() {
        return 'table1_table2';
    }

    static function getColumnNames() {
        return [
            'table1_id',
            'table2_id'
        ];
    }

    static function getAssociativeColumnNames() {
        return [
            'Model1' => 'table1_id',
            'Model2' => 'table2_id'
        ];
    }

}


$model1 = new Model1();
// $mode2 = new Model2();
// $model3 = new Model3();
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

// $model3->setPrimaryKeyValue(2);
// print_r(
//     $model3->update(
//         [
//             'table3col' => 'a updated value 2',
//             'table1_id' => 1
//         ]
//     )
//     ->save())
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
//     ->save())
// ;

// $model1->setPrimaryKeyValue(150);
// print_r(
//     $model1->delete()
//     ->save())
// ;

// $model1->setPrimaryKeyValue(1);
// print_r(
//     $model1->deleteAssociations('Model2')
//     ->save())
// ;


$model1->setPrimaryKeyValue(2);

// print_r($model1->getRow());


// $model1 = $model1->setOrderBy('table3.table1_id', 'DESC');
// print_r($model1->join('Model3', 'table1_id')->getAll());

// print_r($model1->getAll());
// erro
print_r($model1->getAll(2));
// print_r($model1->getAll(2, 2));


// $model1 = $model1->setOrderBy('table1_table2.table2_id', 'DESC');
// print_r($model1->get('Model2'));

// $model1 = $model1->setOrderBy('table4.table1_id', 'DESC');
// print_r($model1->get('Model4'));

// $model4->setPrimaryKeyValue(1);
// print_r($model4->getParent('Model1'));
