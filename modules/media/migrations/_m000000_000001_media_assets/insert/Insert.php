<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace app\modules\media\models;

/**
 * The model class for Insert media.
 */
class Insert extends \yiingine\modules\media\models\Medium
{
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return \Yii::tA(['en' => '{n, plural, =1{Insert}other{Inserts}}', 'fr' => '{n, plural, =1{Encart}other{Encarts}}'], ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->insert_text;
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return ['id', 'type', 'insert_title', 'insert_text'];
    }
}
