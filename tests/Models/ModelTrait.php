<?php

namespace Models;

// to mixin code (not to repeat the the same code)
trait ModelTrait {

    function __construct($databaseSchema) {
        $data = include 'settings/database.php';

        putenv('_ENV=development');
        $environment = getenv('_ENV');
        parent::__construct($environment, $data);

        $this->establishConnection($databaseSchema, 'localhost');
    }

}
