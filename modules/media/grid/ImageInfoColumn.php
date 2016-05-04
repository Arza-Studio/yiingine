<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\grid;

use \Yii;

/**
 * Display the size of an image field.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class ImageInfoColumn extends \yii\grid\DataColumn
{    
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
    
        if(!isset($this->options['style'])) // Use a default style if none is set.
        {
            $this->options['style'] = 'font-weight:normal;font-size:11px;color:gray;';
        }
    }
    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $path = $model->getManager($this->attribute)->getFileUrl($model)[0];
        
        $url = \yii\helpers\Url::to([$path]); // Build the image url.
        $imagePath = Yii::getAlias('@webroot').$path; // Build the image path.
        
        if(file_exists($imagePath)) //If the image exists.
        { 
            // Link to isolate the image in a blank page.
            $string = '<a class="commentedBtn" title="'.Yii::t(__CLASS__, 'Open the image {image} in a new window', ['image' => $model->title]).'" href="'.$url.'" target="_blank">';
            $string .= $model->{$this->attribute};
            $string .= '</a>';
            
            // Display the image's size.
            $size = @getimagesize($imagePath);
            if($size === false) // If something failed.
            {
                return;
            }
            $string .= ' ('.$size[0].'x'.$size[1].' px)';

            return $string;
        }
    }
}
