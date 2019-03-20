<?php

namespace PReTTable\Helpers\SQL;

class ValueAdjuster {

    static function adjust(...$values) {
        $adjusted = [];

        foreach ($values as $value) {
            if (gettype($value) == 'string') {
                array_push($adjusted, "'$value'");
            } else {
                array_push($adjusted, $value);
            }
        }

        return $adjusted;
    }

}
