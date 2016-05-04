<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;
use \yii\helpers\Html;
use \yii\helpers\Url;

/**
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class Background extends \yii\base\Widget 
{
    // Type of layers supported by this widget.
    const TYPE_COLOR = 'color'; 
    const TYPE_GRADIENT = 'gradient';
    const TYPE_IMAGE = 'image'; 
    
    /** @var array the background layers from highest to lowest.
        $layers = [
            [
                'type' => Background::TYPE_IMAGE,
                'url' => string the url to the image file,
                'opacity' => integer the opacity from 0 to 1,
                'css' => string the css,
            ],
            [
                'type' => Background::TYPE_GRADIENT,
                'range' => [
                    [
                        'imageTop' => rbga color, // "{imageTop}" will align the gradient position with the previous image found in layers.
                        '100%' => rbga color,
                    ]
                ]
            ]
        ]
    */
    public $layers = [];
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        ob_start();
    }
    
    /**
     * @inheritdoc
     */
    public function run() 
    {   
        $html = '';
        $endTags = '';
        $css = '';
        $jsReady = '';
        
        foreach($this->layers as $i => $layer)
        {
            // Build and add id to the layer tag attributes (widget id + layer index).
            $id = $this->id.'-'.$i;
            
            $class = 'background'.ucfirst($layer['type']);
            
            switch($layer['type'])
            {
                case self::TYPE_COLOR:
                    $css .= '#'.$id.':before { background-color: '.$layer['color'].' }';
                    break;
                case self::TYPE_GRADIENT:
                    $gradientCss = 'linear-gradient(to bottom';
                    $fadeGradientToImage = false;
                    
                    foreach($layer['range'] as $position => $color)
                    {
                        $gradientCss .= ', '.$color.' '.$position;
                        if($position == '{imageTop}' || $position == '{imageBottom}')
                        {
                            $fadeGradientToImage = true;
                        }
                    }
                    
                    $gradientCss .= ')';
                    
                    if($fadeGradientToImage)
                    {
                        if(isset($this->layers[($i-1)])) // If the previous layer exists
                        {
                            // If the previous layer type is an image
                            if(isset($this->layers[($i-1)]['type']) && $this->layers[($i-1)]['type'] == self::TYPE_IMAGE)
                            {
                                // And if we are able to get the size of the previous layer image
                                if(isset($this->layers[($i-1)]['url']))
                                {
                                    $urlToImage = Url::isRelative($this->layers[($i-1)]['url']) ? Url::to($this->layers[($i-1)]['url'], true) : $this->layers[($i-1)]['url'] ;
                                    if($size = @getimagesize($urlToImage))
                                    {
                                        $functionCall = 'fadeGradientToImage("#'.$id.'", "'.$gradientCss.'", '.$size[0].', '.$size[1].');';
                                        $jsReady .= $functionCall.' $(window).resize(function(){ '.$functionCall.' });';
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        $css .= '#'.$id.' { background: '.$gradientCss.'; }';
                    }
                    
                    break;
                case self::TYPE_IMAGE:                    
                    // If the layer use opacity the image must be attached to :before css selector.
                    if(isset($layer['opacity']) && $layer['opacity'] < 1)
                    {
                        $class .= ' backgroundImageOpacity';
                        $css .= '#'.$id.':before { background-image: url("'.$layer['url'].'"); opacity:'.$layer['opacity'].' }';
                    }
                    else // Otherwise the image is simply attached to the layer.
                    {
                        $css .= '#'.$id.' { background-image: url("'.$layer['url'].'"); }';
                    }
                    break;
                default:
                    throw new \yii\base\Exception($layer['type'].' is not a valid layer type.');
            } 
            
            if(isset($layer['css'])) // If the layer defines additional css.
            {
                if(isset($layer['opacity']) && $layer['opacity'] < 1)
                {
                    $css .= '#'.$id.':before { '.$layer['css'].' }';
                }
                else
                {
                    $css .= '#'.$id.' { '.$layer['css'].' }';
                }
            }
            
            // Open layer tag.
            $html .= Html::beginTag('div', ['class' => $class, 'id' => $id]);
            $endTags .= Html::endTag('div');
        }
        
        $html .= ob_get_clean().$endTags;
        
        // Register the css and javascript required to correctly display layers.
        $this->view->registerCss($css, ['media' => 'screen']);
        $this->view->registerJs($jsReady, \yii\web\View::POS_READY);
        BackgroundAsset::register($this->view);
        
        return $html;
    }
}

/**
 * The asset bundle for the Background widget.
 * */
class BackgroundAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/assets/';
    
    /** @inheritoc */
    public $css = ['background/background.css'];
    
    /** @inheritdoc */
    public $js = ['background/background.js'];
}
