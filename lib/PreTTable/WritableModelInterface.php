<?php

namespace PreTTable;

interface WritableModelInterface extends ModelInterface {

    static function isPrimaryKeySelfIncremental();

}
