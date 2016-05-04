<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\media\models;

use \Yii;

/** 
 * A model for custom fields of type MEDIAPOSITION.
 * */
class MediaPositionField extends \yiingine\modules\customFields\models\CustomField
{ 
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge([
            [['min_size', 'size', 'in_forms', 'required', 'position'], '\yiingine\validators\UnsafeValidator'], // These attributes should not be displayed or used.
            [['in_forms', 'position'], 'default', 'value' => 0], // This field is always in forms but is handled in a special way.
            ['required', 'default', 'value' => 1], // This field is always required.
        ], parent::rules());
    }
    
    /** @return array attribute descriptions (name => description). */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'configuration' => Yii::t(__CLASS__, 'An array associating media types with their grouping attributes.')
        ]);
    }
    
    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return 
'[
    "Model1" => [
       0 => "attribute",
    ],
    "Model2" => [
       0 => "attribute",
    ],
]';
    }
    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('integer').' DEFAULT '.($this->default ? $this->default: 1);
    }
}
