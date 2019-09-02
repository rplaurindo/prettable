<?php

namespace Connections;

use
    PDO,
    PDOException,
    PreTTable\AbstractConnection
;

class PDOConnection extends AbstractConnection {

    function establishConnection($schemaName) {
        $schemaData = $this->data[$schemaName];
        
        $adapter = $schemaData['adapter'];
        $host = $schemaData['host'];

        $dsn = "$adapter:=$host;dbname=$schemaName";

        if (array_key_exists('port', $schemaData)) {
            $port = $schemaData['port'];
            $dsn .= ";port=$port";
        }
        
        $username = $schemaData['username'];
        $password = $schemaData['password'];

        try {
            $connection = new PDO($dsn, $username, $password);
            $connection
                ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e;

            throw new PDOException($e);
        }

        return $connection;
    }

}
