<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\adminSiteToolbar;

use \Yii;
use \rmrevin\yii\fontawesome\FA;

/**
 * AdminSiteToolbar module class.
 */
class Module extends \yiingine\base\Module
{        
    /**
     * @var array default button configurations for the toolbar.
     * */
    public $defaultButtons;
    
    /**
     * @var array user defined button configurations for the toolbar.
     * */
    public $buttons = [];
    
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        $this->moduleMapRoute = false; // No sitemaps for this module.
        
        $this->label = Yii::t(__CLASS__, 'Administration toolbar');
        
        if(!$this->defaultButtons)
        {
            $this->defaultButtons = [
                [
                    'class' => '\yiingine\modules\adminSiteToolbar\widgets\Button',                      
                    'content' => FA::icon('edit'),
                    'options' => [
                        'class' => 'btn adminOverlayInitBtn',
                        'title' => Yii::t(__CLASS__, 'Edit'),
                        'onclick' => '
                            if(!$(this).hasClass("locked"))
                            {
                                $(this).addClass("locked");
                                Overlay.initAll("adminOverlays");
                            }
                            else
                            {
                                $(this).removeClass("locked");
                                Overlay.hideAll("adminOverlays");
                            }'
                    ]
                ],
                [
                    'class' => '\yiingine\modules\adminSiteToolbar\widgets\Button',
                    'content' => FA::icon('eyedropper'),
                    'options' => [
                        'title' => Yii::t(__CLASS__, 'Color palette')
                    ],
                    'visible' => Yii::$app->has('palette') 
                ],
                [
                    'class' => '\yiingine\modules\adminSiteToolbar\widgets\Button',
                    'content' => FA::icon('picture-o'),
                    'options' => [
                        'title' => Yii::t(__CLASS__, 'Check images'),
                    ]
                ],
                [
                    'class' => '\yiingine\modules\adminSiteToolbar\widgets\Button',
                    'content' => FA::icon('undo'),
                    'options' => [
                        'title' => Yii::t(__CLASS__, 'Empty cache')
                    ]
                ],
                [
                    'class' => '\yiingine\modules\adminSiteToolbar\widgets\Button',
                    'content' => FA::icon('area-chart'),
                    'options' => [
                        'title' => Yii::t(__CLASS__, 'Statistics')
                        
                    ]
                ],
                [
                    'class' => '\yiingine\modules\adminSiteToolbar\widgets\Button',
                    'content' => FA::icon('bug'),
                    'options' => [
                        'title' => Yii::t(__CLASS__, 'Report problem'),
                        'href' => \yii\helpers\Url::to(['site/problem-report'])
                    ]
                ]
            ];
        }
        
        parent::init();
    }
}
