<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\parameters;

use \Yii;

/** A custom fields parameter for selecting the requirement of a profile field.*/
class Requirement extends \yiingine\modules\customFields\parameters\Base
{        
    // Requirement constants.
    const REQUIRED_NO = 0;
    const REQUIRED_YES_SHOW_REG = 1;
    const REQUIRED_NO_SHOW_REG = 2;
    const REQUIRED_YES_NOT_SHOW_REG = 3;
    
    /**
     * Render the input for the field.
     * @param CustomField $model the model that owns the input.
     * @return array an ActiveFormStructure compatible form item.
     */
    public function render($model)
    {
        return  [
            'type' => 'dropdownlist',
            'items' =>  [
                self::REQUIRED_NO => Yii::t('generic', 'No'), 
                self::REQUIRED_NO_SHOW_REG => Yii::t(__CLASS__, 'No, but show on registration form'), 
                self::REQUIRED_YES_SHOW_REG => Yii::t(__CLASS__, 'Yes and show on registration form'),
                self::REQUIRED_YES_NOT_SHOW_REG => Yii::t('generic', 'Yes'),
            ],
            'prompt' => Yii::t('generic', 'Select an item'),
            'hint' => Yii::t(__CLASS__, 'The requirement for this field depending on whether the user is registering or not.')
        ];
    }
    
    /** @return string the SQL that defines this field. This sql will be fed to a 
     * CdbSchema::createColumn statement. */
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('integer').' DEFAULT '.self::REQUIRED_YES_SHOW_REG;
    }
    
    /**@return string a user friendly title for this parameter.*/
    public function getTitle()
    {
        return Yii::t(__CLASS__, 'Requirement');
    }
    
    /**Validates this parameter in a model.
     * @param CustomField the model to validate.*/
    public function validate($model)
    {
        $validator = new \yii\validators\NumberValidator();
        $validator->max = self::REQUIRED_YES_NOT_SHOW_REG;
        $validator->integerOnly = true;
        $validator->min = self::REQUIRED_NO;
        $validator->validateAttribute($model, $this->name);
    }
}
