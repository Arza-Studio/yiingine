<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\media\models;

use \Yii;

/**
 * A model for custom fields of type PHPCODE.
 * */
class PhpCodeField extends \yiingine\modules\customFields\models\CustomField
{            
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge([
            [['min_size', 'size', 'configuration'], '\yiingine\validators\UnsafeValidator'], //This attribute should not be displayed or used.
        ], parent::rules());
    }

    /** @return array attribute descriptions (name => description). */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'default' => Yii::t(__CLASS__, 'The field\'s default value. For technical reasons, it cannot be validated so be very careful of what you put here!')
        ]);
    }

    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('text'); 
    }
}
