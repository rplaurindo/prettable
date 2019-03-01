<?php

namespace PReTTable;

interface WritableModelInterface extends ModelInterface {

    static function isPrimaryKeySelfIncremental();

}
