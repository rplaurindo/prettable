<?php

namespace Models\MySQL;

use
    Models\Paginables\MySQLTrait,
    PReTTable\Repository\PDO
;

abstract class AbstractModel extends PDO\AbstractModel {

    use MySQLTrait;

    function __construct($databaseSchema) {
        $data = include 'settings/database.php';

        putenv('_ENV=development');
        $environment = getenv('_ENV');
        parent::__construct($environment, $data);

        $this->establishConnection($databaseSchema, 'localhost');
    }

}
