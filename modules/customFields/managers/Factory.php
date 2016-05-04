<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;

/** 
 * Creates field managers using the factory method pattern. 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class Factory extends \yii\base\Object
{        
    /** @var array the configuration for the different field managers that can be built.*/
    public $managers = [];
    
    /** 
     * @return array type => class the available field types.
     * */
    public function getTypes()
    {
        $fields = [];
    
        foreach($this->managers as $name => $manager)
        {
            $class = $manager['class'];
            $fields[$name] = $class::getFieldModelClass();
        }
    
        return $fields;
    }
    
    /** 
     * Returns a manager for the provided CustomField.
     * @param CustomField $model
     * @param string attribute the name of the attribute
     * @return FieldManager the manager.
     * */
    public function createManager($model, $attribute = null)
    {
        if(!isset($this->managers[$model->typeName()]))
        {
            // A manager for this type was not found.
            throw new \yii\base\Exception('A manager for type "'.$model->typeName().'" was not found.');
        }
        
        $config = $this->managers[$model->typeName()];
        $config['field'] = $model;
        if($attribute)
        {
            $config['attribute'] = $attribute;
        }
        
        return Yii::createObject($config);
    }
}
