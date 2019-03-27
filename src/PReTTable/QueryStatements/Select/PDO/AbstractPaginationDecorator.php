<?php

namespace PReTTable\QueryStatements\Select\PDO;

use
    PReTTable\QueryStatements\Select\AbstractDecorator
;

abstract class AbstractPaginationDecorator extends AbstractDecorator {

    protected $limit;

    protected $pageNumber;

}
