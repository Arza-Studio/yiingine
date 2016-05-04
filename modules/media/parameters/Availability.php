<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\parameters;

use \Yii;

/** A custom fields parameter for setting the scenario of a custom field.*/
class Availability extends \yiingine\modules\customFields\parameters\Base
{                
    /**
     * Render the input for the field.
     * @param CustomField $model the model that owns the input.
     * @return array a Form compatible form item.
     */
    public function render($model)
    {
        return  [
            'type' => 'text',
            'size' => 60,
            'maxlength' => 255,
            'hint' => Yii::t(__CLASS__, 'A comma separated list of the scenarios for which the field should NOT be made available.')
        ];
    }
    
    /** @return string the SQL that defines this field. This sql will be fed to a 
     * CdbSchema::createColumn statement. */
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('string').' DEFAULT ""';
    }
    
    /**@return string a user friendly title for this parameter.*/
    public function getTitle()
    {
        return Yii::t(__CLASS__, 'Availability');
    }
    
    /**Validates this parameter in a model.
     * @param CustomField the model to validate.*/
    public function validate($model)
    {
        //No specific validation to do.
    }
}
