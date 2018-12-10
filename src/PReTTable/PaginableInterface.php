<?php

namespace PReTTable;

interface PaginableInterface {
    
    function getStatement($limit, $pageNumber = 1);
    
}
