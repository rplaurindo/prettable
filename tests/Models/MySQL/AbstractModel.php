<?php

namespace Models\MySQL;

use
    Models\ModelTrait,
    Models\Paginables\MySQLTrait,
    PReTTable\PDO\Repository\Writable
;

abstract class AbstractModel extends Writable\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
