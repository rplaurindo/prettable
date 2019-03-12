<?php

namespace Models\MySQL;

use
    Models\ModelTrait,
    Models\Paginables\MySQLTrait,
    PReTTable\Repository\PDO
;

abstract class AbstractModel extends PDO\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
