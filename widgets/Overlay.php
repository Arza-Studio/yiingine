<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;

/**
 * Overlays the representation of a model on the site with a link to its form
 * in the administation interface. If the model is not provided, the widget will display an
 * overlay on its parent div.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class Overlay extends \yii\base\Widget
{   
    /** @var string the layer id. */
    public $layer = 'overlays';
    
    /** @var string the css selector to target. */
    public $selector;
    
    /** @var array an Url::to() formatted array. */
    public $url;
    
    /** @var string the html content to display inside the overlay. */
    public $content;
    
    /** @var array the html attributes of the overlay. */
    public $options = [];
    
    /** @var string to eval the forced offset top of the overlay. */
    public $offsetTop;
    
    /** @var string to eval the forced offset left of the overlay. */
    public $offsetLeft;
    
    /** @var string to eval (must return boolean) to overide the overlay display rules. */
    public $displayRule;
    
    /** @var boolean set true to initialize the overlay. */
    public $initialize = true;
    
    /** @var boolean set true to display the overlay just after initialize ($initialize must be true). */
    public $displayAfterInit = true;
    
    /** @var string the javascript to execute on click event (normaly used when url is not provided). */
    public $onClick;
    
    /** @var string the javascript to execute before display event. */
    public $beforeDisplay;
    
    /** @var string the javascript to execute after display event. */
    public $afterDisplay;
    
    /** @var string the javascript to execute before hide event. */
    public $beforeHide;
    
    /** @var string the javascript to execute after hide event. */
    public $afterHide;
    
    /**
    * @inheritdoc
    */
    public function run() 
    {        
        OverlayAsset::register($this->view);
        \yiingine\assets\common\JQueryHammerJsAsset::register($this->view);
        
        $selector = addslashes($this->selector);
        $content = addslashes($this->content);
        $options = json_encode($this->options);
        $onClick = addslashes($this->onClick);
        $beforeDisplay = addslashes($this->beforeDisplay);
        $afterDisplay = addslashes($this->afterDisplay);
        $beforeHide = addslashes($this->beforeHide);
        $afterHide = addslashes($this->afterHide);
        $offsetTop = ($this->offsetTop) ? "'".$this->offsetTop."'" : 'false';
        $offsetLeft = ($this->offsetLeft) ? "'".$this->offsetLeft."'" : 'false';
        $displayRule = ($this->displayRule) ? "'".$this->displayRule."'" : 'true';
        $init = $this->initialize ? '.init('.($this->displayAfterInit ? 'true' : '').')': '';
        
        $this->view->registerJs(<<<JS
            $(window).on('load', function(){
                var $this->id = new Overlay('$this->id', {
                    layer: '$this->layer',
                    selector: '$selector',
                    url: '$this->url',
                    content: '$content',
                    options: $options,
                    offsetTop: $offsetTop,
                    offsetLeft: $offsetLeft,
                    displayRule: $displayRule,
                    onClick: '$onClick',
                    beforeDisplay: '$beforeDisplay',
                    afterDisplay: '$afterDisplay',
                    beforeHide: '$beforeHide',
                    afterHide: '$afterHide'
                })$init;
            });
JS
        ,\yii\web\View::POS_END);
    }
}

/** 
 * AssetBundle for the Overlay widget.
 * */
class OverlayAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/assets/overlay';
    
    /** @inheritoc */
    public $css = ['overlay.css'];
    
    /** @inheritdoc */
    public $js = ['overlay.js'];
    
    /** @inheritdoc */
    public $depends = ['yii\web\JqueryAsset'];
}
