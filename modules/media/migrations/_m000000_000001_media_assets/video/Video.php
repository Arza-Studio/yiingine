<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace app\modules\media\models;

/**
 * The model class for Video media.
 */
class Video extends \yiingine\modules\media\models\Medium
{
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return \Yii::tA(['en' => '{n, plural, =1{Video}other{Videos}}', 'fr' => '{n, plural, =1{Vidéo}other{Vidéos}}'], ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->video_text;
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return ['video_title', 'id', 'type'];
    }
}
