<?php

namespace PReTTable\Helpers;

class WhereClause {
    
    private $operator;

    function __construct($operator = 'AND') {
        $this->operator = $operator;
    }
    
    function composeStatementFor(array $params, array $options = []) {
        $defaultOptions = ['containsPartialValues' => false];
        
        $options = array_merge($defaultOptions, $options);
        
        if (array_key_exists('containsPartialValues', $options) && gettype($options['containsPartialValues']) == 'boolean') {
            $containsPartialValues = $options['containsPartialValues'];
        }
        
        if ($containsPartialValues) {
            return implode("", $this->composeContainingPartialValues($params));
        }
        
        return implode("", $this->composeContainingWholeValues($params));
    }
    
    private function composeContainingPartialValues(array $params = []) {
        $composedFields = [];
        
        foreach($params as $column => $value) {
            if (!empty($value)) {
                if (gettype($value) == 'array') {
                    
                    $firstValue = $value[0];
                    $value = array_slice($value, 1);
                    
                    $statement = "$column LIKE '$firstValue'";
                    foreach ($value as $v) {
                        $statement .= " OR $column LIKE '$v'";
                    }
                    
                    if (count($composedFields)) {
                        array_push($composedFields, " {$this->operator} ($statement)");
                    } else {
                        array_push($composedFields, "($statement)");
                    }
                } else {
                    if (count($composedFields)) {
                        array_push($composedFields, " {$this->operator} $column LIKE '$value'");
                    } else {
                        array_push($composedFields, "$column LIKE '$value'");
                    }
                }
            }
        }
        
        return $composedFields;
    }
    
    private function composeContainingWholeValues(array $params = []) {
        $composedFields = [];
        
        foreach($params as $column => $value) {
            if (!empty($value)) {
                if (gettype($value) == 'array') {
                    
                    $firstValue = $value[0];
                    $value = array_slice($value, 1);
                    
                    if (gettype($firstValue) == 'string') {
                        $statement = "$column = '$firstValue'";
                        foreach ($value as $v) {
                            $statement .= " OR $column = '$v'";
                        }
                    } else {
                        $statement = "$column = $firstValue";
                        foreach ($value as $v) {
                            $statement .= " OR $column = $v";
                        }
                    }
                    
                    if (count($composedFields)) {
                        array_push($composedFields, " {$this->operator} ($statement)");
                    } else {
                        array_push($composedFields, "($statement)");
                    }
                } else {
                    if (gettype($value) == 'string') {
                        if (count($composedFields)) {
                            array_push($composedFields, " {$this->operator} $column = '$value'");
                        } else {
                            array_push($composedFields, "$column = '$value'");
                        }
                    } else {
                        if (count($composedFields)) {
                            array_push($composedFields, " {$this->operator} $column = $value");
                        } else {
                            array_push($composedFields, "$column = $value");
                        }
                    }
                    
                }
            }
        }
        
        return $composedFields;
    }
    
}
