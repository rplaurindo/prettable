<?php

namespace PreTTable\Repository;

use
    ArrayObject
    , PreTTable
    , PreTTable\InheritanceRelationship
    , PreTTable\QueryStatements\Component
    , PreTTable\QueryStatements\Decorators\Select
    , PreTTable\Reflection
;

// to supress warnings
// error_reporting(E_ALL ^ E_WARNING);

abstract class AbstractModelBase extends PreTTable\AbstractModel {

    private $setOfThoseContained;

    private $setOfContains;
    
    private $associativeModels;

    function __construct(array $connectionData, $environment = null) {
        parent::__construct($connectionData, $environment);

        $this->setOfThoseContained = new ArrayObject();
        $this->setOfContains = new ArrayObject();
        $this->associativeModels = new ArrayObject();
    }
    
    /**
     * @param string $modelName
     * @param string $type [optional]
     * @param string $leftModelName [optional]
     * @return void
     */
    function join() {
        $modelName = func_get_arg(0);
        $type = 'INNER';
        $leftModelName = null;
        
        if (count(func_get_args()) > 1) {
            $type = func_get_arg(1);
            if (count(func_get_args()) > 2) {
                $leftModelName = func_get_arg(2);
            }
        }
        
        if (
                (
                    $this->doesItContain($modelName)
                    || $this->isItContained($modelName)
                )
                || (isset($leftModelName))
            )
        {
            $model = Reflection::getDeclarationOf($modelName);
            
            if ($this->doesItContain($modelName)) {
                if ($this->doesItContainThrough($modelName)) {
                    $leftModelName = $this->getAssociativeModelNameFrom($modelName);
                    $columnName = $model::getPrimaryKeyName();
                    $leftColumnName = $this->getAssociatedColumn($modelName);
                } else {
                    $leftColumnName = $this->getPrimaryKeyName();
                    $columnName = $this->getAssociatedColumn($modelName);
                    $leftModelName = $this->name;
                }
            } else if ($this->isItContained($leftModelName)) {
                $columnName = $this->getAssociatedColumn($leftModelName);
                $leftColumnName = $model::getPrimaryKeyName();
            } else {
                $columnName = $model::getPrimaryKeyName();
                $leftColumnName = $this->getAssociatedColumn($leftModelName);
            }
            
            parent::join($modelName, $columnName, $leftColumnName, $type, $leftModelName);
        }
        
    }

    protected function contains($modelName, $associatedColumn) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            'PreTTable\IdentifiableModelInterface',
            'PreTTable\AssociativeModelInterface');

        $this->setOfThoseContained
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }

    protected function containsThrough($modelName, $through) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            'PreTTable\IdentifiableModelInterface',
            'PreTTable\AssociativeModelInterface');

        $this->setOfThoseContained
            ->offsetSet($modelName, ['associativeModelName' => $through]);
        
        $this->associativeModels->offsetSet($modelName, $through);
    }

    protected function isContained($modelName, $associatedColumn) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            'PreTTable\IdentifiableModelInterface');

        $this->setOfContains
            ->offsetSet($modelName, ['associatedColumn' => $associatedColumn]);
    }

    protected function getAssociativeModelNameFrom($modelName) {
        if (is_subclass_of($modelName, 'PreTTable\AssociativeModelInterface')) {
            foreach ($this->associativeModels as $currentModelName => $associativeModel) {
                if ($currentModelName == $modelName) {
                    return $associativeModel;
                }
            }
        } else if ($this->associativeModels->offsetExists($modelName)) {
            return $this->associativeModels->offsetGet($modelName);
        }
        
        return null;
    }

    protected function resolvedRelationalSelect($modelName) {
        InheritanceRelationship::throwIfClassIsntA($modelName,
            'PreTTable\IdentifiableModelInterface',
            'PreTTable\AssociativeModelInterface');

        if ($this->doesItContain($modelName)
            || $this->isItContained($modelName)
            ) {

            $associatedModel = Reflection::getDeclarationOf($modelName);
            $associatedTableName = $associatedModel->getTableName();

            $fromStatement = $associatedTableName;
            
            if (!isset($this->selectDecorator)) {
                $this->selectDecorator = new Component('SELECT ');
            }
            
            $this->selectDecorator = new Select($this->selectDecorator, $this, true);
            $this->selectDecorator = new Select($this->selectDecorator, $associatedModel, true);
            
            if ($this->doesItContainThrough($modelName)) {
                $associativeModelName = $this
                    ->getAssociativeModelNameFrom($modelName);
                
                $this->selectDecorator = new Select($this->selectDecorator, $associativeModelName, true);

                $associativeModel = Reflection
                    ::getDeclarationOf($associativeModelName);

                $associativeTableName = $associativeModel->getTableName();
                
                $fromStatement = $associativeTableName;

                $this->join($this->name, 'INNER', $associativeModelName);

                $this->join($modelName, 'INNER', $associativeModelName);
            } else {
                $this->join($this->name, 'INNER', $modelName);
            }
            
            $sql = "\t{$this->selectDecorator->getStatement()}\n\n\tFROM $fromStatement{$this->joinsDecorator->getStatement()}";
            
            return new Component($sql);
        }
        
    }
    
    private function doesItContain($modelName) {
        return $this->setOfThoseContained->offsetExists($modelName);
    }
    
    private function doesItContainThrough($modelName) {
        return ($this->setOfThoseContained->offsetExists($modelName)
            && array_key_exists('associativeModelName',
                $this->setOfThoseContained->offsetGet($modelName)));
    }
    
    private function isItContained($modelName) {
        return $this->setOfContains->offsetExists($modelName);
    }
    
    private function getAssociatedColumn($modelName) {
        if (is_subclass_of($modelName, 'PreTTable\AssociativeModelInterface')) {
            $associativeModel = Reflection::getDeclarationOf($modelName);
            return $associativeModel::getAssociativeColumnNames()[$this->name];
        } else if
        (
            (
                $this->doesItContain($modelName)
                || $this->isItContained($modelName)
            )
        ) {
            if ($this->isItContained($modelName)) {
                return $this->setOfContains
                    ->offsetGet($modelName)['associatedColumn'];
            } else if ($this->doesItContainThrough($modelName)) {
                $associativeModelName = $this
                    ->getAssociativeModelNameFrom($modelName);
                $associativeModel = Reflection
                    ::getDeclarationOf($associativeModelName);
                return $associativeModel
                    ::getAssociativeColumnNames()[$modelName];
            } else {
                return $this->setOfThoseContained
                    ->offsetGet($modelName)['associatedColumn'];
            }
        }
        
        return null;
    }

}
