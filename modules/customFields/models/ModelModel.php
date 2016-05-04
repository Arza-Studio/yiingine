<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

/**
 * Model class for relations between models.
 * */
class ModelModel extends \yii\db\ActiveRecord
{
    /** @var string the name of the table that contains the relation. */
    public static $table;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return self::$table;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['child_id', 'parent_id', 'relation_position', 'relation_id'], 'required'],
            [['child_id', 'parent_id', 'relation_id'], 'integer', 'integerOnly' => true, 'min' => 1],
            ['relation_position', 'integer', 'integerOnly' => true, 'min' => 0]
        ];
    }
}
