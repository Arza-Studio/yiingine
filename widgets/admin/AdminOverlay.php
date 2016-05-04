<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets\admin;

use \Yii;

/**
 * Overlays the representation of a model on the site with a link to its form
 * in the administation interface. If the model is not provided, the widget will display an
 * overlay on its parent div.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class AdminOverlay extends \yiingine\widgets\Overlay
{  
    /** @var \yiingine\interfaceces\AdministrableInterface the model to put the overlay on.*/
    public $model;
    
    /** @var boolean set true to initialize the overlay. */
    public $initialize = false;
    
    /** @var boolean set true to display the overlay just after initialize ($initialize must be true). */
    public $displayAfterInit = false;
    
    /** @var boolean display the model even if it is disabled. */
    public $forceDisplay = false;
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        $this->layer = 'adminOverlays';
        
        $p = Yii::$app->adminPalette;
        $this->view->registerCss('
        body .overlay.adminOverlay { border-color: '.$p->get('AdminDefault').' !important; }
        body .overlay.adminOverlay:before { background-color: '.$p->get('AdminDefault').' !important; }
        ', ['media' => 'screen']);
        
        // Check if the model implement AdministrableInterface
        if($this->model && !($this->model instanceof \yiingine\db\AdministrableInterface))
        {
            throw new \yii\base\InvalidParamException(get_class($this->model).' must implement \\yiingine\\interfaces\\AdministrableInterface!');
        }
        
        // Force the "adminOverlay" css class
        $this->options['class'] = isset($this->options['class']) ? 'adminOverlay '.$this->options['class'] : 'adminOverlay' ;
        
        // Build url to the model.
        if(!$this->url)
        {
            $this->url = \yii\helpers\Url::to($this->model->getAdminUrl());
        }
        // Save the current url so it can be redirected to from the admin.
        $this->url .= (strpos($this->url, '&') !== false ? '&': '?').'returnUrl='.urlencode(Yii::$app->request->url);
        
        // Build admin content
        $this->content = \rmrevin\yii\fontawesome\FA::icon('pencil')->size(\rmrevin\yii\fontawesome\FA::SIZE_4X);
    }
    
    /**
    * @inheritdoc
    */
    public function run() 
    {        
        // If the user isn't logged with administrator right, do not display the widget.
        if(Yii::$app->user->isGuest || !Yii::$app->user->getIdentity()->superuser)
        {
            return;
        }
        
        // Do not display anything if the user is not allowed to administer this model.
        if($this->model && !$this->model->isAccessible())
        {
            return; 
        }
        
        AdminOverlayAsset::register($this->view);
        
        return parent::run();
    }
}

/** 
 * AssetBundle for the AdminOverlay widget.
 * */
class AdminOverlayAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/admin/assets/adminOverlay';
    
    /** @inheritoc */
    public $css = ['adminOverlay.css'];
    
    /** @inheritdoc */
    public $js = ['adminOverlay.js'];
    
    /** @inheritdoc */
    public $depends = ['yiingine\widgets\OverlayAsset'];
}
