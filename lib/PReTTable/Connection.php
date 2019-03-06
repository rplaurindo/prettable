<?php

namespace PReTTable;

use
    PDO,
    PDOException
;

class Connection {

    private $environment;

    private $connection;

    private static $data;

    function __construct() {
        $this->environment = getenv('PReTTable_CONNECTION_ENV');

        if (isset($this->environment)) {
            $this->environment = 'development';
        }
    }

    function establishConnection($databaseSchema, $host = null) {
        $clone = $this->getClone();

        $data = self::$data[$host][$databaseSchema][$clone->environment];

        $adapter = $data['adapter'];

        $username = null;
        if (array_key_exists('username', $data)) {
            $username = $data['username'];
        }

        $password = null;
        if (array_key_exists('password', $data)) {
            $password = $data['password'];
        }

        $dsn = "$adapter:=$host;dbname=$databaseSchema";

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

    static function setData(array $data) {
        self::$data = $data;
    }

    private function getClone() {
        return clone $this;
    }

}
