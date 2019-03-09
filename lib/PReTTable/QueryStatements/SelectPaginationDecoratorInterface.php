<?php

namespace PReTTable\QueryStatements;

interface SelectPaginationDecoratorInterface extends SelectDecoratorInterface {

    function getStatement($limit, $pageNumber = 1);

}
