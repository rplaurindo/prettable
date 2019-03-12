<?php

namespace Models\MySQL\Readonly;

use
    Models\Paginables\MySQLTrait,
    PReTTable\Repository\PDO\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {

    use MySQLTrait;

    function __construct($databaseSchema) {
        $data = include 'settings/database.php';

        putenv('_ENV=development');
        $environment = getenv('_ENV');
        parent::__construct($environment, $data);

        $this->establishConnection($databaseSchema, 'localhost');
    }

}
