<?php

namespace PReTTable\Repository;

use 
    Exception, 
    ArrayObject,
    PReTTable\Reflection
;

class RelationshipMap {
    
    protected $modelName;
    
    protected $setOfThoseContained;
    
    protected $setOfContains;
    
    function __construct($modelName) {
        self::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $this->setOfThoseContained = new ArrayObject();
        $this->setOfContains = new ArrayObject();
    }
    
    static function resolveTableName($modelName) {
        $model = Reflection::getDeclarationOf($modelName);
        
        $tableName = $model::getTableName();
        if (empty ($tableName)) {
            return $modelName;
        }
        
        return $tableName;
    }
    
    static function checkIfModelIs($modelName, ...$classes) {
        $count = 0;
        
        foreach ($classes as $class) {
            if (is_subclass_of($modelName, $class)) {
                $count++;
            }
        }
        
        if (!$count) {
            $classesAsText = implode(" or ", $classes);
            throw new Exception("The model must be a $classesAsText");
        }
        
    }
    
    function getModelName() {
        return $this->modelName;
    }
    
    function contains($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        $this->setOfThoseContained
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    function isItContained($modelName) {
        return $this->setOfThoseContained->offsetExists($modelName);
    }
    
    function containsThrough($modelName, $through) {
        self::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface',
            __NAMESPACE__ . '\AssociativeModelInterface');
        
        $this->setOfThoseContained->offsetSet($modelName, ['associativeModelName' => $through]);
    }
    
    function isItContainedThrough($modelName) {
        return ($this->setOfThoseContained->offsetExists($modelName)
            && array_key_exists('associativeModelName',
                $this->setOfThoseContained->offsetGet($modelName)));
    }
    
    function isContained($modelName, $associatedColumn) {
        self::checkIfModelIs($modelName,
            __NAMESPACE__ . '\IdentifiableModelInterface');
        
        $this->setOfContains
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }
    
    function doesItContain($modelName) {
        return $this->setOfContains->offsetExists($modelName);
    }
    
    function getAssociativeModelNameOf($modelName) {
        if ($this->setOfThoseContained->offsetExists($modelName)) {
            $relationshipData = $this->setOfThoseContained->offsetGet($modelName);
            
            if (array_key_exists('associativeModelName', $relationshipData)) {
                return $relationshipData['associativeModelName'];
            }
        }
        
        return null;
    }
    
}
