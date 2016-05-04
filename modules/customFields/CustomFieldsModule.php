<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields;

use \Yii;

/**
 * CustomFields module class.
 */
class CustomFieldsModule extends \yiingine\base\Module
{        
    /**
     * @var string the name of the database table this module will store custom fields.
     * */
    public $tableName;
    
    /**
     * @var string the name of the model class custom fields will be added to.
     * */
    public $modelClass;
    
    /** @var array FieldParameter component configuration for this module's CustomField parameters.*/
    public $fieldParameters = [];
    
    /** Initialize the module. */
    public function init()
    {
        $this->label = Yii::t(__CLASS__, 'Custom Fields');
        
        parent::init();
        
        if(!$this->module) //If this modules does not have a parent.
        {
            // This module cannot be used in standalone mode.
            throw new \yii\base\Exception('customFields module cannot be used as a standalone module.');
        }
    }
    
    /**@return array the field parameters for this module. Implements a crude factory pattern.*/
    public function getFieldParameters()
    {
        $params = [];
        
        // Loop in the parameter configuration and create each parameter.
        foreach($this->fieldParameters as $param => $config)
        {            
            $config['module'] = $this;
            $component = Yii::createObject($config);
            $component->init();
            $params[$param] = $component;
        }
        
        return $params;
    }
    
    /** @return boolean if the current user can access this module.*/
    public function checkAccess()
    {
        return Yii::$app->user->can('CustomField-'.$this->module->id.'-view') || parent::checkAccess();
    }
}
