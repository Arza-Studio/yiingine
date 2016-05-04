<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace app\modules\media\models;

use \Yii;

/**
 * The model class for Document media.
 */
class Document extends \yiingine\modules\media\models\Medium
{
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return \Yii::tA(['en' => '{n, plural, =1{Document}other{Documents}}', 'fr' => '{n, plural, =1{Document}other{Documents}}'], ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->document_text;
    }
    
    /**
     * @inheritdoc
     * */
    public function getThumbnail()
    {
        return (!$this->thumbnail &&
            $this->document_file &&
            $pathinfo = pathinfo(\yii\helpers\Url::to($this->getManager("document_file")->getFileUrl()))
        ) ? Yii::$app->assetManager->publish(Yii::getAlias("@yiingine/assets/admin/images/icons"))[1]."/".$pathinfo["extension"].".png" : 
        ($this->thumbnail ? $this->getManager('thumbnail')->getFileUrl() : false);
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return ['id', 'type', 'document_title', 'document_text', 'document_file'];
    }
}
