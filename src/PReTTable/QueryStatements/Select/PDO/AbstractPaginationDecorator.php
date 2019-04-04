<?php

namespace PReTTable\QueryStatements\Select\PDO;

use
    PReTTable\QueryStatements\AbstractDecorator
;

abstract class AbstractPaginationDecorator extends AbstractDecorator {

    protected $limit;

    protected $pageNumber;

}
