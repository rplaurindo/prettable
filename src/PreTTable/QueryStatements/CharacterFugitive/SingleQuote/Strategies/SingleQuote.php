<?php

namespace PreTTable\QueryStatements\CharacterFugitive\SingleQuote\Strategies;

use
    PreTTable\QueryStatements\CharacterFugitive\StrategyInterface
;

class SingleQuote implements StrategyInterface {

    function getEscaped(array $values) {

        $adjusted = [];
        
        foreach ($values as $value) {
            
            if (gettype($value) === 'string') {
                $value = preg_replace("/'/", "''", $value);
            }
            
            $adjusted[] = $value;
            
        }
        
        return $adjusted;
        
    }
    
}
