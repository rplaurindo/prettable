<?php

namespace PReTTable\Helpers;

class StringEncoding {

    private $charset;

    function __construct($charset = 'utf-8') {
        $this->charset = strtolower($charset);
    }

    function encode(array $record) {
        $encoded = [];
        foreach ($record as $k => $v) {
            if (
//                 isset($v)
//                 &&
                is_string($v)
                && !empty($v)
                && mb_detect_encoding($v) != $this->charset
            ) {
                if ($this->charset == 'utf-8') {
                    $encoded[$k] = utf8_encode($v);
                }
            } else {
                $encoded[$k] = $v;
            }
        }
        return $encoded;
    }

    function collectionEncode(array $rows) {
        return array_map(function ($r) {
            return $this->encode($r);
        }, $rows);
    }

}
