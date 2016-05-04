<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\grid;

use \Yii;

/**
 * Display a resized image wrapped with a zoombox link from a IMAGE type attribute.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class ImageColumn extends \yii\grid\DataColumn
{    
    /** @var int the height of the thumbnail.*/
    public $height = 90;
    
    /** 
     * @inheritdoc 
     * */
    public function init()
    {
        $this->filter = false;
        $this->enableSorting = false;
        
        parent::init();
    }
    
    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if($model->{$this->attribute}) // If an image is defined.
        {
            // Get Image Path.
            $value = str_replace(Yii::getAlias('@webroot'), '', $model->{$this->attribute});
            
            if(!empty(Yii::$app->request->baseUrl) && strpos($value, Yii::$app->request->baseUrl) === 0) // Remove the baseUrl from the image url.
            {
                // Base url can only be replaced once because it could be repeated in the image url.
                $value = substr_replace($value, '', 0, strlen(Yii::$app->request->baseUrl));
            }
            
            $imagePath = (file_exists(Yii::getAlias('@webroot').$value)) ? $value : $model->getManager($this->attribute)->getFileUrl($model)[0];
            
            if(file_exists(Yii::getAlias('@webroot').$imagePath)) // Make sure the image exists.
            {           
                return \yii\helpers\Html::a(
                    \yii\helpers\Html::tag('img', '', [
                        'style' => 'margin:3px 0 3px 0;',
                        'src' => Yii::$app->imagine->getThumbnail(Yii::getAlias('@webroot').$imagePath, $this->height)
                    ]),
                    Yii::$app->request->baseUrl.$imagePath,
                    [
                        'title' => Yii::t(__CLASS__, 'Zoom on the image'),
                        'data-pjax' => 0
                    ]
                );
            }
            else
            {
                return Yii::t(__CLASS__, 'No Image File');
            }
        }
        else
        {
            return '-';
        }
    }
}
