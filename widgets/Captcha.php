<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

 /**
 * A wrapper around Yii's Captcha widget to add instructions on how to use a captcha.
 * @author Antoine Mercier-Lintea
 */
class Captcha extends \yii\base\Widget
{    
    /** 
     * @var array options to be passed to the captcha component. 
     * */
    public $options = [ ];
    
    /** 
     * @var Model the model for which the captcha is. 
     * */
    public $model;
    
    /** 
     * @var string the attribute that holds the captcha answer.
     * */
    public $attribute = 'captcha';
    
    /** 
     * @var string the route to the captcha action. 
     * */
    public $captchaAction = 'Captcha.get';
    
    /** 
     * @inheritdoc 
     * */
    public static function actions()
    {
        return [
            'Captcha.get' => [
                'class' => '\yii\captcha\CaptchaAction',
                'backColor' => 0xFFFFFF,
                'height' => 50,
                'width' => 130,
            ]
        ];    
    }
    
    /** 
     * @inheritdoc 
     * */
    public function init()
    {
        if(!\yii\captcha\Captcha::checkRequirements()) //If the requirements for displaying a Captcha are not met.
        {
            throw new \yii\base\Exception('Missing requirements for displaying captchas.');
        }
        
        parent::init();
    }
    
    /** 
     * @inheritdoc 
     * */
    public function run()
    {   
        return $this->render('captcha', [
            'options' => $this->options,
            'captchaAction' => $this->captchaAction,
            'model' => $this->model,
            'attribute' => $this->attribute
        ]);
    }
}
