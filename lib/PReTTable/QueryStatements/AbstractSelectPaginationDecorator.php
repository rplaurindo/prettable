<?php

namespace PReTTable\QueryStatements;

abstract class AbstractSelectPaginationDecorator extends AbstractSelectDecorator {

    abstract function getStatement($limit, $pageNumber = 1);

}
