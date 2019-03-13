<?php

namespace Models\Repository\PDO\MySQL;

use
    Models\ModelTrait,
    Models\PDO\Paginables\MySQLTrait,
    PReTTable\PDO\Repository\Writable
;

abstract class AbstractModel extends Writable\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
