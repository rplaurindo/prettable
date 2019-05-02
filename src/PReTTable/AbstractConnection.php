<?php

namespace PReTTable;

abstract class AbstractConnection {

    protected $data;

    function __construct(array $data) {
        $this->data = $data;
    }

    abstract function establishConnection($schemaName);

}
