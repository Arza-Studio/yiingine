<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type Float.*/
class FloatField extends IntegerField
{    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('float').' DEFAULT '.
               ($this->default ? $this->default: 0); 
    }
}
