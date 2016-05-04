<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\models;

/**
 * The registration form is used only during registration so supplement the user class
 * with a captcha code.
 */
class RegistrationForm extends User 
{
    /** @var string holds the captcha answer.*/
    public $captcha;
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        $this->scenario = 'registration';
    }
    
    /**
    * @inheritdoc
    */
    public function rules() 
    {
        if(!\Yii::$app->getModule('users')->doCaptchaAtRegistration)
        {
            return parent::rules();
        }
        
        //Add the captcha field.
        return array_merge(parent::rules(), [
            [['captcha'], 'captcha', 'captchaAction' => 'users/register/Captcha.get'],
            [['captcha'], 'required'],
        ]);
    }
}
