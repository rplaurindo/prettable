<?php

require 'autoload.php';

use PReTTable\IdentifiableModelInterface;
use PReTTable\AbstractModel;
use PReTTable\AssociativeModelInterface;

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
        
        $this->containsThrough('Model2', 'AssociativeModel');
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

class Model2 implements IdentifiableModelInterface {
    
    function __construct() {
        
    }
    
    static function getTableName() {
        return 'table_2';
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
            'column1'
        ];
    }
    
}

class AssociativeModel implements AssociativeModelInterface {
    
    function __construct() {
        
    }
    
    static function getTableName() {
        return 'associative_table';
    }
    
    static function getColumns() {
        return [
            'table_1_id',
            'table_2_id'
        ];
    }
    
    static function getAssociativeKeys() {
        return [
            'Model1' => 'table_1_id',
            'Model2' => 'table_2_id'
        ];
    }
    
}


$model = new Model1();
// print_r($model->getRow(2));
// print_r($model->getRow('column1', 'value 2'));

// echo $model->create(['column1' => 'a value'])
//     ->commit()
// ;
 
// echo $model->createAssociations('Model2', 1,
//     [
//         'table_2_id' => 1
//     ],
//     [
//         'table_2_id' => 2
//     ]
//     )->commit()
// //     )
// ;

// echo $model
//     ->create(['column1' => 'a value'])
//     ->createAssociations('Model2',
//         [
//             'table_2_id' => 1
//         ],
//         [
//             'table_2_id' => 2
//         ]
//         )->commit()
// //         )
// ;

// echo $model->update(49, ['column1' => 'a updated value'])
//     ->commit()
// ;

// como fazer verifica��o se h� chave nova relacionada? Talvez o melhor caminho seja pegar todas as chaves associadas, guardar em um array e depois usar
// in_array para chevar, caso n�o esteja ele dever� ser tratado como uma nova associa��o, sen�o basta fazer update nos atributos. Ainda tem o caso dele 
// estar nas chaves associadas, mas n�o estar mais nos par�metros, caso em que deve ser deletado.   
// echo $model->updateAssociations('Model2', 1,
//     [
//         'table_2_id' => 1
//     ],
//     [
//         'table_2_id' => 3
//     ]
//     )->commit()
// //     )
// ;

// echo $model->delete('id', 44, 45, 46, 47)
//     ->commit()
// ;

// echo $model->deleteAssociations('Model2', 2)
//     ->commit()
// ;

print_r($model->getAssociatedKeys('Model2', 76));
