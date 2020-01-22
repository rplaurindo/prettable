<?php

namespace PreTTable\QueryStatements\CharacterFugitive;

interface StrategyInterface {

    function getEscaped(array $values);

}
