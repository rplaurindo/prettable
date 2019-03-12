<?php

namespace Models\MySQL\Readonly;

use
    Models\ModelTrait,
    Models\Paginables\MySQLTrait,
    PReTTable\Repository\PDO\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
