<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\parameters;

use \Yii;

/** A custom fields parameter for the media module to set which media type owns which fields.*/
class Owners extends \yiingine\modules\customFields\parameters\Base
{        
    /**
     * Render the input for the field.
     * @param CustomField $model the model that owns the input.
     * @return array a CForm compatible form item.
     */
    public function render($model)
    {
        return  [
            'type' => 'text',
            'size' => 60,
            'maxlength' => 255,
            'hint' => Yii::t(__CLASS__, 'A comma separated list of the media types this field is associated with.')
        ];
    }
    
    /** @return string the SQL that defines this field. This sql will be fed to a 
     * CdbSchema::createColumn statement. */
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('string');
    }
    
    /**@return string a user friendly title for this parameter.*/
    public function getTitle()
    {
        return Yii::t(__CLASS__, 'Owners');
    }
    
    /**Validates this parameter in a model.
     * @param CustomField the model to validate.*/
    public function validate($model)
    {
        //If the attribute was given as an array.
        if(is_array($model->{$this->name}))
        {
            $model->{$this->name} = implode(' ', $model->{$this->name});
        }
        
        $validator = new \yii\validators\RegularExpressionValidator(['pattern' => '/^[A-Za-z0-9_, ]+$/u']);
        $validator->validateAttribute($model, $this->name);
        
        /*The length of this parameter cannot be mode than 255.
         * See DbSchema::getColumnType for "string".*/
        $validator = new \yii\validators\StringValidator();
        $validator->max = 255;
        $validator->validateAttribute($model, $this->name);
        
        //Remove spaces for this field.
        $model->{$this->name} = str_replace(' ', '', $model->{$this->name});
    }
}
