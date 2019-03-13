<?php

namespace Models\Repository\PDO\MySQL\Readonly;

use
    Models\ModelTrait,
    Models\PDO\Paginables\MySQLTrait,
    PReTTable\PDO\Repository\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
