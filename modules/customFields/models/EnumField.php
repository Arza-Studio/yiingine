<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type ENUM.*/
class EnumField extends CustomField
{
    const LENGTH = 255;
      
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge([
            [['min_size', 'size'], '\yiingine\validators\UnsafeValidator'], // This attribute should not be displayed or used.
            ['configuration', 'required'],
        ], parent::rules());
    }

    /**
     * Validate the configuration attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateConfiguration($attribute, $params)
    {
        if(!$this->$attribute) //If configuration has not been specified.
        {
            return; //Error.
        }

        $configuration = $this->getConfigurationArray();
        
        /*For the ENUM type, configuration must be array('data' => array('key1' => 'value1', 'key2' => 'value2'))*/
        
        if(!isset($configuration['data']))
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
        }
    }

    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return 
'[
    "data" => [
        "key1" => "value1",
        "key2" => "value2",
        "key3" => "value3",
    ]
]';
    }
    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return 'VARCHAR('.self::LENGTH.') DEFAULT '.($this->default !== '' ? '"'.$this->default.'"' : '""'); 
    }
}
