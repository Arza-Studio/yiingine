<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\widgets;

use \Yii;

/**
 * A widget for displaying media as thumbnails.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class Thumbnail extends Renderer
{
    // Ratio available for img and iframe tag optimization.
    const RATIO_1BY1 = '1by1'; 
    const RATIO_4BY3 = '4by3'; 
    const RATIO_16BY9 = '16by9';
    
    /**
     * @var string the name of the view to use.
     * */
    public $viewName = '_thumbnail';
    
    /** 
     * @var string the layout of the thumnail. 
     * Header, image, description and footer are the recommended items in a
     * thumbnail view and managed by default in the layout.
     * */
    public $layout = '{header}{image}{description}{footer}';
    
    /** 
     * @var array additional or replacement items in the layout.
     * $layout = '{header}{description}{elem1}{elem2}{footer}'
     * $layoutItems = [
        '{elem1}' => $elem1,
        '{elem2}' => $elem2,
        '{description}' => $description
    ] 
     * */
    public $layoutItems = [];
    
    /** 
     * @var array the html attributes of the article tag. 
     * */
    public $options = [];
    
    # Header
    
    /** 
     * @var string the tag used for the heading (title) of the thumbnail. 
     * */
    public $headerTag = 'h2';
    
    # Image
    
    /** 
     * @var boolean true to use a zoom on click event on the img tag in {image}
     * and to display zoom button in {footer}.
     * */
    public $imageZoom = true;
        
    /** 
     * @var string the url of the link wrapping the the img tag in {image}.
     * If no $imageLinkUrl is provided $imageZoom will be used.
     * */
    public $imageLinkUrl;
    
    /** 
     * @var boolean true to lazy load img tag in {image}.
     * */
    public $imageLazyLoad = true;
        
    /** 
     * @var mixed : boolean true to optimize img tags in {image}
     * or RATIO_1BY1, RATIO_4BY3, RATIO_16BY9 to optimize it with a forced ratio.
     * */
    public $imageOptimizeImgs = self::RATIO_4BY3;
    
    /** 
     * @var boolean true to use an overlay covering {image}.
     * */
    public $imageOverlay = true;
    
    /** 
     * @var string the content of the overlay covering {image}.
     * */
    public $imageOverlayContent;
    
    # Description 
    
    /** 
     * @var mixed : integer the number of characters to display 
     * or false to display the entire string in {description}.
     * W3C recommends that the meta description must not exceed 150 characters.
     * */
    public $descriptionTruncate = 150;
    
    /** 
     * @var boolean true to lazy load img and iframe tags in {description}.
     * */
    public $descriptionLazyLoad = false;
    
    /** 
     * @var mixed : boolean true to optimize img tags in {description}
     * or RATIO_1BY1, RATIO_4BY3, RATIO_16BY9 to optimize it with a forced ratio.
     * */
    public $descriptionOptimizeImgs = self::RATIO_4BY3;
    
    /** 
     * @var mixed : boolean true to optimize iframe tags in {description}
     * or RATIO_1BY1, RATIO_4BY3, RATIO_16BY9 to optimize it with a forced ratio.
     * */
    public $descriptionOptimizeIframes = self::RATIO_4BY3;
    
    # Footer
    
    /** 
     * @var string the layout of the footer
     * {url} is replace with a button for displaying the untruncated resource (getUrl() or modal).
     * */
    public $footerLayout = '{share}{url}';
    
    
   /** 
     * @var array additional or replacement items in the footer layout.
     * $layout = '{btn1}{url}'
     * $layoutItems = [
        '{elem1}' => $btn1,
    ] 
     * */
    public $footerLayoutItems = [];
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        ThumbnailAsset::register($this->view);
    }
}

/**
 * The asset bundle for the Thumbnail widget.
 * */
class ThumbnailAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/modules/media/widgets/assets/';
    
    /** @inheritoc */
    public $css = ['thumbnail/_thumbnail.css'];
    
    public $depends = [
        //'yii\bootstrap\BootstrapAsset',
        //'yii\bootstrap\BootstrapPluginAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
