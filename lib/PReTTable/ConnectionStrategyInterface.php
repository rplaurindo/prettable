<?php

namespace PReTTable;

interface ConnectionStrategyInterface {

    function establishConnection($schemaName, $host = null);

}
