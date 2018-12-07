<?php

namespace PReTTable;

class PDOStatementQueryMap extends SearchQueryMap {
    
    private $statement;
    
    private $values;
    
    private $update;
    
    private $set;
    
    private $deleteFrom;
    
    function __construct($modelName) {
        parent::__construct($modelName);
    }
    
    function getAssociativeModelNameOf($modelName) {
        if ($this->containsSet->offsetExists($modelName)) {
            $relationshipData = $this->containsSet->offsetGet($modelName);
            
            if (array_key_exists('associativeModelName', $relationshipData)) {
                return $relationshipData['associativeModelName'];
            }
        }
        
        return null;
    }
    
    function select($modelName, $primaryKeyValue = null) {
        self::checkIfModelIs($modelName, __NAMESPACE__ . '\IdentifiableModelInterface', __NAMESPACE__ . '\AssociativeModelInterface');
        
        $clone = $this->getClone();
        
        $clone->associatedModelName = $modelName;
        $clone->associatedModel = Reflection::getDeclarationOf($modelName);
        $clone->associatedTableName = self::resolveTableName($modelName);
        
        if ($clone->containsSet->offsetExists($modelName) 
            || $clone->isContainedSet->offsetExists($modelName)) {
                
            $selectStatement = new SelectStatement($modelName);
            $clone->select = $selectStatement->mount(true);
            
            $clone->from = $clone->associatedTableName;
            
            if ($clone->containsSet->offsetExists($modelName)) {
                if (array_key_exists('associativeModelName', 
                        $clone->containsSet->offsetGet($modelName))
                    ) {
                    $clone->associativeModelName = $clone->getAssociativeModelNameOf($modelName);
                    $clone->associativeModel = Reflection::getDeclarationOf($clone->associativeModelName);
                    
                    $clone->associativeTableName = self::resolveTableName($clone->associativeModelName);
                    $clone->from = $clone->associativeTableName;
                    
                    $clone->join($clone->modelName, $clone->primaryKey);
                    $clone->join($modelName, $clone->associatedModel::getPrimaryKeyName());
                    
                    $associativeColumn = $clone->associativeModel::getAssociativeKeys()[$clone->modelName];
                    if (isset($primaryKeyValue) && !empty($primaryKeyValue)) {
                        $clone->whereClause = "$clone->associativeTableName.$associativeColumn = $primaryKeyValue";
                    }
                } else {
                    $clone->join($clone->modelName, $clone->primaryKey);
                    
                    $associatedColumn = $clone->containsSet->offsetGet($modelName)['associatedColumn'];
                    if (isset($primaryKeyValue) && !empty($primaryKeyValue)) {
                        $clone->whereClause = "$clone->associatedTableName.$associatedColumn = $primaryKeyValue";
                    }
                }
            } else {
                $associatedColumn = $clone->isContainedSet->offsetGet($modelName)['associatedColumn'];
                
                $clone->join($clone->modelName, $associatedColumn);
                
                if (isset($primaryKeyValue) && !empty($primaryKeyValue)) {
                    $clone->whereClause = "$clone->tableName.$associatedColumn = $primaryKeyValue";
                }
            }
        }
        
        return $clone;
    }
    
    function insertIntoAssociation($modelName, ...$rows) {
        $clone = $this->getClone();
        
        $associativeModelName = $clone->containsSet->offsetGet($modelName)['associativeModelName'];
        
        $insertIntoStatement = new InsertIntoStatement($associativeModelName, ...$rows);
        $clone->insertInto = $insertIntoStatement->getInsertIntoStatement();
        $clone->values = $insertIntoStatement->getValuesStatement();
        
        return $clone;
    }
    
    function getMap() {
        $map = parent::getMap();
        
        if (isset($this->insertInto)) {
            $map['insertInto'] = $this->insertInto;
        }
        
        if (isset($this->values)) {
            $map['values'] = $this->values;
        }
        
        if (isset($this->update)) {
            $map['update'] = $this->update;
        }
        
        if (isset($this->set)) {
            $map['set'] = $this->set;
        }
        
        if (isset($this->deleteFrom)) {
            $map['deleteFrom'] = $this->deleteFrom;
        }
        
        return $map;
    }
    
}
