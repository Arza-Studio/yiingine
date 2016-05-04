<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace app\modules\media\models;

/**
 * The model class for Image media.
 */
class Image extends \yiingine\modules\media\models\Medium
{
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return \Yii::tA(['en' => '{n, plural, =1{Image}other{Images}}', 'fr' => '{n, plural, =1{Image}other{Images}}'], ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->image_text;
    }
    
    /**
     * @inheritdoc
     */
    public function getThumbnail()
    {
        return $this->getManager('image_image')->getFileUrl();
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return ['image_title', 'id', 'type'];
    }
}
