<?php

namespace PReTTable\Connections;

use
    PDO,
    PDOException,
    PReTTable\Connection
;

class PDOConnection extends Connection {

    function __construct($environment = null) {
        parent::__construct($environment);
    }

    function establishConnection($schemaName, $host = null) {
        parent::establishConnection($schemaName, $host);

        $dsn = "$this->adapter:=$this->host;dbname=$schemaName";

        try {
            $connection = new PDO($dsn, $this->username, $this->password);
            $connection
                ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e;

            throw new PDOException($e);
        }

        return $connection;
    }


}
