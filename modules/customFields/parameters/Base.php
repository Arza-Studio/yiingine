<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\parameters;

use \Yii;

/** Abstract class for a field parmeter. Field parameters are "fields within fields" that
 * allow further customization of the customField module. For extreme cases, a
 * customField module could be nested inside another one but in most situations, this
 * should prove sufficient.*/
abstract class Base extends \yii\base\Object
{            
    /**@var string the name of this parameter (the name of the column in the database.*/
    public $name;
    
    /**@var boolean if this parameter is required.*/
    public $required = false;
    
    /**@var CustomFieldsModule the module that owns this component.*/
    public $module;
    
    /** Initialize the manager.*/
    public function init()
    {
        parent::init();
        
        if(!$this->name)
        {
            throw new \yii\baseException('A name for this parameter must be set.');
        }
    }
    
    /**
     * Render the input for the field.
     * @param CustomField $model the model that owns the input.
     * @return array a CForm compatible form item.
     */
    public abstract function render($model);
    
    /** @return string the SQL that defines this field. This sql will be fed to a 
     * CdbSchema::createColumn statement. */
    public abstract function getSql();
    
    /**Validates this parameter in a model.
     * @param CustomField the model to validate.*/
    public abstract function validate($model);
}
