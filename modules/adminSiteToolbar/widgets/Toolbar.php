<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\adminSiteToolbar\widgets;

use \Yii;

/**
 * The widget for the AdminSiteToolbar.
 * */
class Toolbar extends \yii\base\Widget
{
    /** @var string the layer id. */
    public $id = 'adminSiteToolbar';
    
    /** @var boolean to active or not the first button on first toolbar shown. */
    public $activeFirstButton = true;
    
    /** @var string the javascript to execute on toolbar show event. */
    public $onToolbarShow;
    
    /** @var string the javascript to execute on toolbar shown event. */
    public $onToolbarShown;
    
    /** @var string the javascript to execute on toolbar hide event. */
    public $onToolbarHide;
    
    /** @var string the javascript to execute on toolbar hidden event. */
    public $onToolbarHidden;
    
    /**
     * @inheritdoc
     * */
    public function init()
    {        
        // If the module has no been configured.
        if(!Yii::$app->getModule('adminSiteToolbar'))
        {
            throw new \yii\base\Exception('adminSiteToolbar module has not been configured!');   
        }
        
        parent::init();
        
        ob_start();
    }
    
    /**
     * @inheritdoc
     * */
    public function run()
    {        
        // Only show this toolbar for logged in admins.
        if(Yii::$app->user->isGuest || !Yii::$app->user->getIdentity()->superuser)
        {
            return ob_get_clean();
        }
        
        ToolbarAsset::register($this->view);
        
        $buttons = [];
        
        // Render each default button.
        foreach(Yii::$app->getModule('adminSiteToolbar')->defaultButtons as $button)
        {
            $buttons[] = Yii::createObject($button)->run();
        }
        
        // Render each site defined button.
        foreach(Yii::$app->getModule('adminSiteToolbar')->buttons as $button)
        {
            $buttons[] = Yii::createObject($button)->run();
        }
        
        return $this->render('toolbar', ['buttons' => $buttons]).\yii\helpers\Html::tag('div', ob_get_clean(), ['class'=>'adminSiteToolbarCanvas']);
    }
}

/** 
 * AssetBundle for the Overlay widget.
 * */
class ToolbarAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/modules/adminSiteToolbar/widgets/assets/toolbar';
    
    /** @inheritoc */
    public $css = ['toolbar.css'];
    
    /** @inheritdoc */
    public $js = ['toolbar.js'];
    
    /** @inheritdoc */
    public $depends = ['comdvas\jasnybootstrap\JasnyBootstrapAsset'];
}
