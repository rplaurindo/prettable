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
            'table1col',
            'table1col1'
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
            'table2col',
            'table2col1'
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


$model = new Model1();

// for ($i = 1; $i <= 10; $i++) {
//     $model = $model->create(['table1col' => "a value $i"]);
// }
// echo $model->commit();

// $mode2 = new Model2();

// for ($i = 1; $i <= 10; $i++) {
//     $mode2 = $mode2->create(['table2col' => "a value $i"]);
// }
// echo $mode2->commit();

// echo $model->createAssociations('Model2', 1,
//     [
//         'table2_id' => 1
//     ],
//     [
//         'table2_id' => 2
//     ]
//     )->commit()
// //     )
// ;

// echo $model
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
// print_r($model->getRow('table1col', 'a value 2'));

// print_r($model->getRow(2));

// print_r($model->getAll());
// print_r($model->getAll(2));
print_r($model->getAll(2, 2));

// print_r($model->get(2, 'Model2'));

// echo $model->update(49, ['column1' => 'a updated value'])
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
