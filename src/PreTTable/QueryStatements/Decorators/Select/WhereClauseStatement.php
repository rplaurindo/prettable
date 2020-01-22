<?php

namespace PreTTable\QueryStatements\Decorators\Select;

use
    PreTTable\WhereClause\InvolvedTableNames
;

class WhereClauseStatement {
    
    protected $statement;
    
    protected $involvedTableNames;
    
    protected $options = [];
    
    function __construct(InvolvedTableNames $involvedTableNames = null) {
        $this->statement = '';
        
        $this->involvedTableNames = $involvedTableNames;
        
        $this->options = [
            'comparisonOperator' => '=',
            'logicalOperator' => 'AND'
        ];
    }
    
    function between($columnName, $start, $end, $options = []) {
        $clone = $this->getClone();
        
        $columnStatement = $columnName;
        
        if (isset($clone->involvedTableNames)) {
            $tableName = $clone->involvedTableNames->getTableNameOfColumnName($columnName);
            
            if (isset($tableName)) {
                $columnStatement = "$tableName.$columnName";
            }
        }
        
        $statement = "$columnStatement BETWEEN $start AND $end";
        $clone->addsStatement($statement, $options);
        
        return $clone;
    }
    
    function getStatement() {
        return $this->statement;
    }
    
    protected function addsStatement($statement, $options = []) {
        if (array_key_exists('logicalOperator', $options)) {
            $logicalOperator = $options['logicalOperator'];
        } else {
            $logicalOperator = $this->options['logicalOperator'];
        }
        
        if (empty($this->statement)) {
            $this->statement .= "$statement";
        } else {
            $this->statement .= "\n\n\t\t\t$logicalOperator $statement";
        }
    }
    
    protected function getClone() {
        return clone $this;
    }
    
}
