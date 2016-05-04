<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\parameters;

use \Yii;

/** A custom fields parameter for setting the visibility of a profile form.*/
class Visible extends \yiingine\modules\customFields\parameters\Base
{        
    //Visibility constants.
    const VISIBLE_ADMIN_INTERFACE = 4;
    const VISIBLE_ALL = 3;
    const VISIBLE_REGISTER_USER = 2;
    const VISIBLE_ONLY_OWNER = 1;
    const VISIBLE_NO = 0;
    
    /**
     * Render the input for the field.
     * @param CustomField $model the model that owns the input.
     * @return array an ActiveFormStructure compatible form item.
     */
    public function render($model)
    {
        return  [
            'type' => 'dropdownlist',
            'items' => [
                self::VISIBLE_NO => Yii::t(__CLASS__, 'Hidden'),
                self::VISIBLE_ONLY_OWNER => Yii::t(__CLASS__, 'Owner only'),
                self::VISIBLE_REGISTER_USER => Yii::t(__CLASS__, 'Registered users'),
                self::VISIBLE_ALL => Yii::t(__CLASS__, 'For all'),
                self::VISIBLE_ADMIN_INTERFACE => Yii::t(__CLASS__, 'Within the administration interface')
            ],
            'prompt' => Yii::t('generic', 'Select an item'),
            'hint' => Yii::t(__CLASS__, 'The field\'s visibility within the site.')
        ];
    }
    
    /** @return string the SQL that defines this field. This sql will be fed to a 
     * CdbSchema::createColumn statement. */
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('integer').' DEFAULT '.self::VISIBLE_NO;
    }
    
    /** @return string a user friendly title for this parameter. */
    public function getTitle()
    {
        return Yii::t(__CLASS__, 'Visibility');
    }
    
    /**Validates this parameter in a model.
     * @param CustomField the model to validate.*/
    public function validate($model)
    {
        $validator = new \yii\validators\NumberValidator();
        $validator->max = self::VISIBLE_ADMIN_INTERFACE;
        $validator->integerOnly = true;
        $validator->min = self::VISIBLE_NO;
        $validator->validateAttribute($model, $this->name);
    }
}
