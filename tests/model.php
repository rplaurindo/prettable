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
// echo $model1->commit();

// for ($i = 1; $i <= 10; $i++) {
//     $mode2 = $mode2->create(['table2col' => "a value $i"]);
// }
// echo $mode2->commit();

// echo $model1->createAssociations('Model2', 1,
//     [
//         'table2_id' => 1
//     ],
//     [
//         'table2_id' => 2
//     ]
//     )->commit()
// //     )
// ;

// echo $model1
//     ->create(['column1' => 'a value'])
//     ->createAssociations('Model2',
//         [
//             'table2_id' => 1
//         ],
//         [
//             'table2_id' => 2
//         ]
//         )->commit()
// //         )
// ;

// if there isn't a self-incremental primary key
// print_r($model1->getRow('table1col', 'a value 2'));

// print_r($model1->getRow(2));

// print_r($model1->getAll());
// print_r($model1->getAll(2));
// print_r($model1->join('Model3', 'table1_id')->getAll(2));
// print_r($model1->getAll(2, 2));

// print_r($model1->get(2, 'Model2'));
// print_r($model1->get(2, 'Model2', 2, 3));

// echo $model1->update(10, ['table1col' => 'a updated value'])
//     ->commit()
// ;

// como fazer verificação se há chave nova relacionada? Talvez o melhor caminho seja pegar todas as chaves associadas, guardar em um array e depois usar
// in_array para chevar, caso não esteja ele deverá ser tratado como uma nova associação, senão basta fazer update nos atributos. Ainda tem o caso dele 
// estar nas chaves associadas, mas não estar mais nos parâmetros, caso em que deve ser deletado.   
// echo $model->updateAssociations('Model2', 1,
//     [
//         'table2_id' => 1
//     ],
//     [
//         'table2_id' => 3
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

// print_r($model->getAssociatedKeys('Model2', 76));

// echo $model->deleteFromAssociation('Model2', 76, 1)->commit();
