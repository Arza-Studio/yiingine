<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type BOOLEAN.*/
class BooleanField extends CustomField
{    
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge([
            [['min_size', 'size', 'configuration'], '\yiingine\validators\UnsafeValidator'], //These attributes should not be displayed or used.
        ], parent::rules());
    }

    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('boolean').' DEFAULT "'.
               ($this->default ? $this->default: 0).'"'; 
    }
}
