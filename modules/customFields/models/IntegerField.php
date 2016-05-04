<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type INTEGER.*/
class IntegerField extends CustomField
{ 
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge([
            ['min_size', '\yiingine\validators\UnsafeValidator'], // This attribute should not be displayed or used.
            ['size', 'required'],
            ['size', 'integer', 'min' => 1, 'max' => 11, 'integerOnly' => true]
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
        
        $configuration = $this->getConfigurationArray();
        
        // For Integer, configuration must be in the form of array("minimum" => 1, maximum" => null), where null means no maximum of minimum.
        if($configuration['minimum'] !== null && !isset($configuration['minimum'])
           || $configuration['maximum'] !== null && !isset($configuration['maximum'])
           || ($configuration['maximum'] !== null && $configuration['minimum'] !== null && $configuration['maximum'] < $configuration['minimum'])
        )
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
        }
    }
    
    /** @return array attribute descriptions (name => description). */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'size' => Yii::t(__CLASS__, 'Defines the display width (ex: INT(SIZE) in MySQL).'),
        ]);
    }
    
    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return 
'array(
    "minimum" => 0, // null for no minimum.
    "maximum" => 99, // null for no maximum.
)';
    }
    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return 'int('.$this->size.') DEFAULT '.($this->default ? $this->default: 0);
    }
}
