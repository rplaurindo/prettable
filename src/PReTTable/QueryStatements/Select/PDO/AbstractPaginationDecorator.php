<?php

namespace PReTTable\QueryStatements\Select\PDO;

abstract class AbstractPaginationDecorator extends AbstractDecorator {

    protected $limit;

    protected $pageNumber;

}
