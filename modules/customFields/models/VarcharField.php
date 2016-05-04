<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type VARCHAR.*/
class VarcharField extends CustomField
{
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge([
            ['size', 'required'],
            ['size', 'integer', 'min' => 1],
        ], parent::rules());
    }
    
    /**
     * Validate the configuration attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateConfiguration($attribute, $params)
    {
        if($this->hasErrors($attribute))
        {
            return;
        } // If there are errors, do not validate.
    
        if(!$this->$attribute)
        {
            return; // Do not validate if no configuration was provided.
        }
        
        $configuration = $this->getConfigurationArray();
        
        if(!isset($configuration['warningLimit']) || 
            !isset($configuration['errorLimit']) || 
            !isset($configuration['mode']) || 
            !isset($configuration['locked']))
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
            return;
        }
        
        // If the mode is invalid.
        if(!in_array($configuration['mode'], ['additional', 'substractive']))
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
            return;
        }
        
        // If the error limit is smaller than the warning limit.
        if($configuration['warningLimit'] !== null && 
            $configuration['errorLimit'] !== null && 
            $configuration['errorLimit'] <= $configuration['warningLimit'])
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
            return;
        }
    }
    
    /**
     * Validate the size attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateSize($attribute, $params)
    {
        $validator = new \yii\validators\NumberValidator();
        $validator->min = $this->min_size ? $this->min_size: null;
        $validator->validate($this, $attribute);
    }
    
    /**
     * Validate the min_size attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateMinSize($attribute, $params)
    {
        $validator = new \yii\validators\NumberValidator();
        $validator->max = $this->size ?  $this->size : null;
        $validator->validate($this, $attribute);
    }

    /** @return array attribute descriptions (name => description). */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'size' => Yii::t(__CLASS__, 'The maximum size of this field.'),
            'min_size' => Yii::t(__CLASS__, 'The minimum size of this field. Set to 0 if none.'),
        ]);
    }

    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return 
'array(
    "warningLimit" => 150, // The number of characters that can be entered before a warning appears.
    "errorLimit" => 200,  // The number of characters that can be entered before an error appears.
    "mode" => "additional | substractive", // If the character countdown is additive or substractive.
    "locked" => true // If more characters than errorLimit can be entered.
)';
    }
    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return 'VARCHAR('.$this->size.') DEFAULT "'.($this->default ? $this->default: '').'"'; 
    }
}
