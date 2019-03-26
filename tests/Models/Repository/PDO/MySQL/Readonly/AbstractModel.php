<?php

namespace Models\Repository\PDO\MySQL\Readonly;

use
    Models\ModelTrait,
    Models\PDO\Paginables\MySQLTrait,
    PReTTable\Repository\PDO\Readonly\QuestionMarkPlaceholder
;

abstract class AbstractModel extends QuestionMarkPlaceholder\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
