<?php

namespace Repository\PDO;

use
    PDO\ModelTraitProxy
    , PreTTable\Repository
;

abstract class AbstractModelBase extends Repository\AbstractModelBase {

    use ModelTraitProxy;

}
