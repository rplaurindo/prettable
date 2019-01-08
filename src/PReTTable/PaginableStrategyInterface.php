<?php

namespace PReTTable;

interface PaginableStrategyInterface {
    
    function getStatement($limit, $pageNumber = 1);
    
}
