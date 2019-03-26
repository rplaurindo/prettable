<?php

namespace Models\Repository\PDO\MySQL;

use
    Models\ModelTrait,
    Models\PDO\Paginables\MySQLTrait,
    PReTTable\Repository\PDO\Writable\QuestionMarkPlaceholder
;

abstract class AbstractModel extends QuestionMarkPlaceholder\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
