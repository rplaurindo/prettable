<?php

namespace Models\Repository\PDO\MySQL;

use
    Models\ModelTrait,
    Models\PDO\Paginables\MySQLTrait,
    PReTTable\Repository\PDO\Writable
;

abstract class AbstractModel extends Writable\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
