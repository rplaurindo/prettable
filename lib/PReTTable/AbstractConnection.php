<?php

namespace PReTTable;

abstract class AbstractConnection {

    protected $environment;

    protected $host;

    protected $adapter;

    protected $username;

    protected $password;

    private static $data;

    function __construct($environment = null) {
        if (!isset($environment)) {
            $environment = 'development';
        }

        $this->environment = $environment;
        $this->username = null;
        $this->password = null;
    }

    abstract function establishConnection($schemaName, $host = null);

    static function setData(array $data) {
        self::$data = $data;
    }

    function setEnvironment($environment) {
        $this->environment = $environment;
    }

    function resolveConnectionData($schemaName, $host = null) {
        if (isset($host)) {
            $this->host = $host;
        }

        $environmentData = self::$data[$this->host][$schemaName][$this->environment];

        $this->adapter = $environmentData['adapter'];

        if (array_key_exists('username', $environmentData)) {
            $this->username = $environmentData['username'];
        }

        if (array_key_exists('password', $environmentData)) {
            $this->password = $environmentData['password'];
        }
    }

}
