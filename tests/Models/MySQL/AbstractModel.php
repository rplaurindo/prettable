<?php

namespace Models\MySQL;

use
    Models\ModelTrait,
    Models\Paginables\MySQLTrait,
    PReTTable\Repository\PDO\Writable
;

abstract class AbstractModel extends Writable\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
