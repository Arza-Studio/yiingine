<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\widgets;

use \Yii;

/**
 * A widget for displaying media as modal dialogs.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class Modal extends Renderer
{
    // Ratio available for img and iframe tag optimization.
    const RATIO_1BY1 = '1by1'; 
    const RATIO_4BY3 = '4by3'; 
    const RATIO_16BY9 = '16by9';
    
    /**
     * @var string the name of the view to use.
     * */
    public $viewName = '_modal';
    
    /** 
     * @var string the layout of the thumnail. 
     * Header, content and footer are managed by default in the layout.
     * Note : the first position of {header} and the last position of {footer} will be forced in any case.
     * */
    public $layout = '{header}{content}{footer}';
    
    /** 
     * @var array additional or replacement items in the layout.
     * $layout = '{header}{content}{elem1}{elem2}{footer}'
     * $layoutItems = [
        '{elem1}' => $elem1,
        '{elem2}' => $elem2,
        '{content}' => $content
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
    
    # Content
    
    /** 
     * @var boolean true to lazy load img and iframe tags in {content}.
     * */
    public $contentLazyLoad = false;
    
    /** 
     * @var mixed : boolean true to optimize img tags in {content}
     * or RATIO_1BY1, RATIO_4BY3, RATIO_16BY9 to optimize it with a forced ratio.
     * */
    public $contentOptimizeImgs = self::RATIO_4BY3;
    
    /** 
     * @var mixed : boolean true to optimize iframe tags in {content}
     * or RATIO_1BY1, RATIO_4BY3, RATIO_16BY9 to optimize it with a forced ratio.
     * */
    public $contentOptimizeIframes = self::RATIO_4BY3;
    
    # Footer
    
    /** 
     * @var string the layout of the footer
     * {url} is replace with a button for displaying the untruncated resource (getUrl() or modal).
     * */
    public $footerLayout = '{share}{close}';
    
    
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
        
        ModalAsset::register($this->view);
    }
}
    
/**
 * The asset bundle for the Thumbnail widget.
 * */
class ModalAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/modules/media/widgets/assets/';
    
    /** @inheritoc */
    public $css = ['modal/_modal.css'];
    
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
