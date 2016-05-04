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
use \yii\web\View;
use \keltstr\simplehtmldom\SimpleHTMLDom;

/**
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class LazyLoad extends \yii\base\Widget 
{
    
    /** @var string the html code to treat. */
    public $html;
    
    /** @var string the loader to add in the parent content top of the object to lazy-load. */
    public $loader = '<svg viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd" stroke-width="2"><circle cx="22" cy="22" r="1"><animate attributeName="r" begin="0s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="0s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" /></circle><circle cx="22" cy="22" r="1"><animate attributeName="r" begin="-0.9s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="-0.9s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" /></circle></g></svg>';
    
    /** 
     * Return the isolated img tags in an array. 
     */
    protected function getImgs()
    {
        // Imgs tags isolation (we are not using html parser here to be able replace exact string).
        if(preg_match_all('#(<img[^>]+>)#i', $this->html, $result) >= 1)
        {
            return $result[0];
        }

        return false;
    }
    
    protected function getIframes()
    {
        // Imgs tags isolation (we are not using html parser here to be able replace exact string).
        if(preg_match_all('#(<iframe[^>]+>)#i', $this->html, $result) >= 1)
        {
            return $result[0];
        }

        return false;
    }
    
    /**
     * @inheritdoc
     */
    public function run() 
    { 
        LazyLoadAsset::register($this->view);
        
        $registerJs = false;
        
        // If img tags has been found.
        if($imgs = $this->getImgs())
        {
            foreach($imgs as $img)
            {
                // Get the image attributes via html parsing (see : http://simplehtmldom.sourceforge.net/).
                $options = SimpleHTMLDom::str_get_html($img)->find('img', 0)->getAllAttributes();
                // If the img tag has a src attribute.
                if(isset($options['src']))
                {
                    $urlToImage = Url::isRelative($options['src']) ? Url::to($options['src'], true) : $options['src'] ;
                    // If the image file exists :
                    if(file_exists(str_replace(Yii::$app->request->hostInfo.Yii::$app->request->baseUrl.'/', '', $urlToImage)))
                    { 
                        // Get image size.
                        $size = @getimagesize($urlToImage);
                        // Add original width and height in data attribute.
                        $options['data-width'] = $size[0];
                        $options['data-height'] = $size[1];
                        // Rebuild img tag.
                        $src = $options['src'];
                        unset($options['src']);
                        $modifiedImg = Html::img($src, $options);
                        // Replace src attribute by data-src attribute
                        $modifiedImg = str_replace('src="', 'data-src="', $modifiedImg);
                        $modifiedImg = str_replace('data-data-src="', 'data-src="', $modifiedImg); // To prevent double optimization.
                        // Replace the img tag in the html.
                        $this->html = str_replace($img, $modifiedImg, $this->html);
                        // Activate js registration
                        $registerJs = true;
                    }
                }
            }
        }
        // If img tags has been found.
        if($iframes = $this->getIframes())
        {
            foreach($iframes as $iframe)
            {
                // Replace src attribute by data-src attribute
                $modifiedIframe = str_replace('src="', 'data-src="', $iframe);
                $modifiedIframe = str_replace('data-data-src="', 'data-src="', $modifiedIframe); // To prevent double optimization.
                // Replace the img tag in the html.
                $this->html = str_replace($iframe, $modifiedIframe, $this->html);
                // Activate js registration
                $registerJs = true;
            }
        }
        if($registerJs)
        {
            $this->view->registerJs('var lazyLoadLoader = \''.$this->loader.'\';', View::POS_HEAD);
            $this->view->registerJs('lazyLoadInit();', View::POS_READY);
        }
        
        return $this->html;
    }
}

/**
 * The asset bundle for the LazyLoad widget.
 * */
class LazyLoadAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/assets/';
    
    /** @inheritoc */
    public $css = ['lazyLoad/lazyLoad.css'];
    
    /** @inheritdoc */
    public $js = ['lazyLoad/lazyLoad.js'];
}
