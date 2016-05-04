<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

/** 
 * A base class for relational field behaviors.
 * */
abstract class BaseRelational extends Base
{          
    /** 
     * @inheritdoc 
     * */
    public function __get($name)
    {
        // Override of parent implementation to check if the relation is being accessed.
        $relations = $this->getRelations();
        
        return isset($relations[$name]) ? $relations[$name]:  parent::__get($name);
    }
    
    /**
     * @inheritdoc
     * */
    public function __call($name, $params)
    {
        // Override of parent implementation to get the relation as a method.
        
        try
        {
            return parent::__call($name, $params);
        }
        catch(\yii\base\UnknownMethodException $e)
        {
            $relation = substr($name, 3);
            $relations = $this->getRelations();
            
            // Maybe are relation is being fetched.
            if(isset($relations[$relation]))
            {
                return $relations[$relation];
            }
            
            throw $e; // This really was an invalid call.
        }
    }
    
    /**
     * @inheritdoc
     * */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        // Override of parent implementation to check if the relation is being accessed.
        return in_array($name, array_keys($this->getRelations())) ||
            parent::canGetProperty($name, $checkVars, $checkBehaviors);
    }
    
    /**
     * @inheritdoc
     * */
    public function hasMethod($name, $checkBehaviors = true)
    {
        // Override of parent implementation to check if the relation is being accessed.
        return in_array(substr($name, 3), array_keys($this->getRelations())) ||
            parent::hasMethod($name, $checkBehaviors);
    }
    
    /** 
     * @return [ActiveQueryInterface|ActiveQuery] the relational query objects. 
     * */
    protected abstract function getRelations();
}
