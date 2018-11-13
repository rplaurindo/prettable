<?php

namespace PReTTable;

class Model {
    
    private $tableName;
    
    private $table;
    
    private $contains;
    
    private $isContained;
    
    private $select;
    
    private $map;
    
//     function __construct($tableName) {
    function __construct() {
        
        $this->tableName = get_class($this);
        $this->table = self::getClassConstant($this->tableName);
        
//         $this->tableName = $tableName;
//         if (!is_subclass_of($this->tableName, 'AbstractTable')) {
//             throw new Exception('The table must be an AbstractTable');
//         }
        
        $this->contains = [];
        $this->isContained = [];
        
        $this->select = new ArrayObject();
    }
    
    function where() {
        $this->map = [
            'select' => '',
            'from' => '',
            'where' => ''
        ];
    }
    
    function find($field, $value = '') {
        $this->map = [
            'select' => '',
            'from' => '',
            'where' => ''
        ];
        
        if (count(func_get_args()) == 1) {
            $value = $field;
            $field = $this->table::getPrimaryKey();
        }
        
        self::attachesAt($this->select, self::mountFieldsStatement($this->tableName));
        
        $this->map['select'] = implode(", ", $this->select->getArrayCopy());
        $this->map['from']   = $this->tableName;
        $this->map['where']  = "$this->tableName.{$field} = '$value'";
        
        return $this->map;
    }
    
    function getAll() {
        $this->map = [
            'select' => '',
            'from' => ''
        ];
        
        self::attachesAt($this->select, self::mountFieldsStatement($this->tableName));
        
        $this->map['select'] = implode(", ", $this->select->getArrayCopy());
        $this->map['from']   = $this->tableName;
        
        return $this->map;
    }
    
    function select($tableName) {
        
        $fields = [];
        
        $this->map = [
            'select' => '',
            'from' => '',
            'joins' => []
        ];
        
        $relatedTable = self::getClassConstant($tableName);
        
        if (array_key_exists($tableName, $this->contains) || 
            array_key_exists($tableName, $this->isContained)) {
            
            self::attachesAt($this->select, self::mountFieldsStatement($tableName, true));
            $this->map['select'] = implode(", ", $this->select->getArrayCopy());
            $this->map['from']   = $tableName;
            
            if (array_key_exists($tableName, $this->contains)) {
                if (array_key_exists('associativeTable', $this->contains[$tableName])) {
                    $this->map['from'] = $this->contains[$tableName]['associativeTable'];
                    $associativeTableName = $this->map['from'];
                    $associativeTable = self::getClassConstant($associativeTableName);
                    
                    $fk = $associativeTable::getForeignKeyOf($this->tableName);
                    
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->table::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
                    $fk = $associativeTable::getForeignKeyOf($tableName);
                    
                    array_push($this->map['joins'],
                        "$tableName ON $tableName.{$relatedTable::getPrimaryKey()} = $associativeTableName.{$fk}");
                } else {
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->table::getPrimaryKey()} = $tableName.{$relatedTable::getPrimaryKey()}");
                }
            } else {
                if (array_key_exists('associativeTable', $this->isContained[$tableName])) {
                    $this->map['from'] = $this->isContained[$tableName]['associativeTable'];
                    $associativeTableName = $this->map['from'];
                    $associativeTable = self::getClassConstant($associativeTableName);
                    
                    $fk = $associativeTable::getForeignKeyOf($this->tableName);
                    
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->table::getPrimaryKey()} = $associativeTableName.{$fk}");
                    
                    $fk = $associativeTable::getForeignKeyOf($tableName);
                    
                    array_push($this->map['joins'],
                        "$tableName ON $tableName.{$relatedTable::getPrimaryKey()} = $associativeTableName.{$fk}");
                } else {
                    array_push($this->map['joins'],
                        "$this->tableName ON $this->tableName.{$this->table::getPrimaryKey()} = $tableName.{$relatedTable::getPrimaryKey()}");
                }
            }
            
        }
        
        return $this->map;
        
    }
    
    function contains($tableName, $foreignKey, $through = '') {
        
        if (!is_subclass_of($tableName, 'AbstractTable')) {
            throw new Exception('The table must be an AbstractTable');
        }

        $this->contains[$tableName] = [
            'foreignKey' => $foreignKey
        ];
        
        $relatedTableSpecifications = $this->contains[$tableName];
        
        if (!empty($through)) {
            if (!is_subclass_of($through, 'AbstractAssociativeTable')) {
                throw new Exception('The associative table must be an AbstractAssociativeTable');
            }
            $this->contains[$tableName]['associativeTable'] = $through;
        }
        
    }
    
    function isContained($tableName, $foreignKey, $through = '') {
        
        if (!is_subclass_of($tableName, 'AbstractTable')) {
            throw new Exception('The table must be an AbstractTable');
        }
        
        $this->isContained[$tableName] = [
            'foreignKey' => $foreignKey
        ];
        
        $relatedTableSpecifications = $this->isContained[$tableName];
        
        if (!empty($through)) {
            $this->isContained[$tableName]['associativeTable'] = $through;
        }
    }
    
    private static function mountFieldsStatement($tableName = '', $attachTable = false) {
        $fields = [];
        
        $table = self::getClassConstant($tableName);
        if ($attachTable) {
            return SQLHelp::mountFieldsStatement($table::getFields(), $tableName);
        }
        
        return SQLHelp::mountFieldsStatement($table::getFields());
    }
    
    private static function attachesAt(ArrayObject $list, $statement) {
        if (!in_array($statement, $list->getArrayCopy())) {
            $list->append($statement);
        }
    }
    
    private static function getClassConstant($tableName) {
        $reflection = new ReflectionClass($tableName);
        return $reflection->newInstanceWithoutConstructor();
    }
    
}
