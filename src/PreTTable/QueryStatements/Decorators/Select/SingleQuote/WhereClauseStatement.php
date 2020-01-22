<?php

namespace PreTTable\QueryStatements\Decorators\Select\SingleQuote;

use
    PreTTable\Helpers\SQL
    , PreTTable\QueryStatements\CharacterFugitive
    , PreTTable\QueryStatements\Decorators\Select
    , PreTTable\WhereClause
;


class WhereClauseStatement extends Select\WhereClauseStatement {
    
    private $characterFugitiveStrategy;
    
    function __construct(WhereClause\InvolvedTableNames $involvedTableNames = null) {
        parent::__construct($involvedTableNames);
        
        $this->characterFugitiveStrategy = new CharacterFugitive\StrategyContext(new CharacterFugitive\SingleQuote\Strategies\SingleQuote());
    }
    
    function like($columnName, $value, $options = []) {
        $clone = $this->getClone();
        
        $value = $this->characterFugitiveStrategy->getEscaped([$value])[0];
        
        $value = SQL\ValueAdjuster::adjust([$value])[0];
        
        $columnStatement = $columnName;
        
        if (isset($clone->involvedTableNames)) {
            $tableName = $clone->involvedTableNames->getTableNameOfColumnName($columnName);
            
            if (isset($tableName)) {
                $columnStatement = "$tableName.$columnName";
            }
        }
        
        $statement = "($columnStatement LIKE $value)";
        $clone->addsStatement($statement, $options);
        
        return $clone;
    }
    
//     if there are equal columns, it is correct to add a statement manually for each one that repeats. These columns should not be mapped.
    private function addsStatement2($columnName, $value, $options = []) {
        if (array_key_exists('comparisonOperator', $options)) {
            $comparisonOperator = $options['comparisonOperator'];
        } else {
            $comparisonOperator = $this->options['comparisonOperator'];
        }
        
        $columnStatement = $columnName;
        
        if (isset($this->involvedTableNames)) {
            $tableName = $this->involvedTableNames->getTableNameOfColumnName($columnName);
            if (isset($tableName)) {
                $columnStatement = "$tableName.$columnName";
            }
        }
        
        if (gettype($value) === 'array') {
            if (count($value)) {
                $value = $this->characterFugitiveStrategy->getEscaped($value);
                
                $value = SQL\ValueAdjuster::adjust($value);
                
                $valuesStatement = implode(", ", $value);
                $statement = "($columnStatement IN ($valuesStatement))";
            }
        }
        else {
            $value = $this->characterFugitiveStrategy->getEscaped([$value])[0];
            
            $value = SQL\ValueAdjuster::adjust([$value])[0];
            
            $statement = "($columnStatement $comparisonOperator $value)";
        }
        
        if (isset($statement)) {
            $this->addsStatement($statement, $options);
        }
        
        return $this;
    }
    
    function addsStatements(array $params, $options = []) {
        $clone = $this->getClone();
        
        foreach($params as $columnName => $value) {
            $clone->addsStatement2($columnName, $value, $options);
        }
        
        return $clone;
    }
    
}
