<?php

namespace PReTTable;

class Connection implements ConnectionStrategyInterface {

    private static $data;

    protected $environment;

    protected $host;

    protected $adapter;

    protected $username;

    protected $password;

    function __construct($environment = null) {
        if (!isset($environment)) {
            $environment = 'development';
        }

        $this->environment = $environment;
        $this->username = null;
        $this->password = null;
    }

    static function setData(array $data) {
        self::$data = $data;
    }

    function establishConnection($schemaName, $host = null) {
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

    protected function getClone() {
        return clone $this;
    }

}
