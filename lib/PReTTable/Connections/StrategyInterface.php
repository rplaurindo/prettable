<?php

namespace PReTTable\Connections;

interface StrategyInterface {

    function establishConnection($schemaName, $host = null);

}
