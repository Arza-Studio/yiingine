<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type Text.*/
class TextField extends VarcharField
{
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge(CustomField::rules(), [ // Override the modifications of VarcharField.
            ['size', 'default', 'value' => 0],
            ['size', 'required'],
            ['size', 'integer', 'min' => 0]
        ]);
    }
    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('text'); 
    }
    
    /** @return array attribute descriptions (name => description). */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'size' => Yii::t(__CLASS__, 'The maximum size of this field. Set to 0 if none.'),
        ]);
    }
}
