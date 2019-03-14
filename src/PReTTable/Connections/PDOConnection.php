<?php

namespace PReTTable\Connections;

use
    PDO,
    PDOException,
    PReTTable\AbstractConnection
;

class PDOConnection extends AbstractConnection {

    function establishConnection($schemaName, $host = null) {
        parent::resolveConnectionData($schemaName, $host);

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
