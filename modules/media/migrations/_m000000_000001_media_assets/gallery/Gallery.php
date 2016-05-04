<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace app\modules\media\models;

/**
 * The model class for Gallery media.
 */
class Gallery extends \yiingine\modules\media\models\Medium
{
    /**
     * @inheritdoc
     * */
    public static $includeInSiteMap = true;
    
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return \Yii::tA(['en' => '{n, plural, =1{Gallery}other{Galleries}}', 'fr' => '{n, plural, =1{Galerie}other{Galeries}}'], ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->gallery_text;
    }
    
    /**
     * @inheritdoc
     */
    public function getThumbnail()
    {
        return $this->gallery_items ? $this->gallery_items[0]->getManager('image_image')->getFileUrl() : false;
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return ['gallery_title', 'id', 'type'];
    }
}
