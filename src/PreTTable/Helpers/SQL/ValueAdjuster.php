<?php

namespace PreTTable\Helpers\SQL;

class ValueAdjuster {

    static function adjust(array $values) {
        $adjusted = [];

        foreach ($values as $value) {
            
            switch (gettype($value)) {
                case 'string': {
                    $value = preg_replace("/'/", "''", $value);
                    
                    if (mb_detect_encoding($value) === 'UTF-8') {
                        $value = utf8_decode($value);
                    }
                    
                    $value = "'$value'";
                }
                break;
                case 'NULL': {
                    $value = 'NULL';
                }
            }
            
            array_push($adjusted, $value);
        }

        return $adjusted;
    }
    
}
