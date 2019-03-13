<?php

namespace Models\MySQL\Readonly;

use
    Models\ModelTrait,
    Models\Paginables\MySQLTrait,
    PReTTable\PDO\Repository\Readonly
;

abstract class AbstractModel extends Readonly\AbstractModel {

    use ModelTrait;

    use MySQLTrait;

}
