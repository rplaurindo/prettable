<?php

namespace PReTTable;

interface QueryStatementStrategyInterface {

    function getStatement(array $attributes);
    
}
