<?php

namespace PreTTable;

interface IdentifiableModelInterface extends ModelInterface {

    static function getPrimaryKeyName();

}
