<?php

namespace PReTTable;

abstract class AbstractConnection {

    protected $environment;

    protected $host;

    protected $port;

    protected $adapter;

    protected $username;

    protected $password;

    private static $data;

    function __construct(array $data, $environment) {
        $this->environment = $environment;
        $this->port = null;
        $this->username = null;
        $this->password = null;

        self::$data = $data;
    }

    abstract function establishConnection($schemaName);

    static function setData(array $data) {
        self::$data = $data;
    }

    protected function resolveConnectionData($schemaName) {
        $environmentData = self::$data[$schemaName][$this->environment];

        $this->host = $environmentData['host'];

        if (array_key_exists('port', $environmentData)) {
            $this->port = $environmentData['port'];
        }

        $this->adapter = $environmentData['adapter'];

        if (array_key_exists('username', $environmentData)) {
            $this->username = $environmentData['username'];
        }

        if (array_key_exists('password', $environmentData)) {
            $this->password = $environmentData['password'];
        }
    }

}
