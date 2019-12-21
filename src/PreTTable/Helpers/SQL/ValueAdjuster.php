<?php

namespace PreTTable\Helpers\SQL;

class ValueAdjuster {

    static function adjust(array $values) {
        $adjusted = [];

        foreach ($values as $value) {
            $value = preg_replace("/'/", "''", $value);
            
            if (gettype($value) === 'string') {
                
                if (mb_detect_encoding($value) === 'UTF-8') {
                    $value = utf8_decode($value);
                }
                
                array_push($adjusted, "'$value'");
            } else {
                array_push($adjusted, $value);
            }
        }

        return $adjusted;
    }

}
