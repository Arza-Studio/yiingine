<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\widgets;

use \Yii;

/**
 * A widget that lets a user log in/out from anywhere within the website.
 * As opposed to a login page, it is meant to be always present and in so being
 * can intercept and consume HTTP request.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class LoginBox extends \yii\base\Widget
{   
    /** @var switchType can be : */
    const DROPDOWN = 0;
    const MODAL = 1;
    
    /**@var integer the type of switch to display the different languages.*/
    public $switchType = self::DROPDOWN;
    
    /** @var string the text for the username label. */
    public $usernameLabel = null;
    
    /** @var array the html attributes for the username text field. */
    public $usernameHtmlOptions = ['class' => 'form-control'];
    
    /** @var string the text for the password label. */
    public $passwordLabel = null;
    
    /** @var array the html attributes for the password field. */
    public $passwordHtmlOptions = ['class' => 'form-control'];
    
    /** @var boolean whether to enable remember login feature.
     * If you set this to true, please make sure you also set CWebUser.allowAutoLogin
     * to be true in the application configuration. */
    public $enableRememberMe = true;
    
    /** @var string the text for the remember me label. */
    public $rememberMeLabel = null;
    
    /** @var array the route to a register page for users who do not have an account.
     * If set to null, the link is not shown in the login form. */
    public $registerUrl = ['/users/register'];
    
    /** @var string the text for the register button. */
    public $registerText = null;
    
    /** @var array the route to a forgot password page.
    * If set to null, the link is not shown in the login form. */
    public $forgotPasswordUrl = ['/users/profile/recover'];
    
    /** @var string the text for the password recovery link. */
    public $forgotPasswordText = null;
    
    /** @var string the text for the login button. */
    public $loginText = null;
    
    /** @var array the link used when logging out. */
    public $logoutUrl = ['/users/default/logout'];
    
    /** @var array the url a user that logged out is redirected to. Leave to null to
     * redirect the user to the same url he logged out from.*/
    public $returnLogoutUrl = null;
    
    /** @var string the text for the admin button. */
    public $adminText = null;
    
    /** @var string the text for the profile button. */
    public $profileText = null;
    
    /** @var string the text for the logout button. */
    public $logoutText = null;
    
    /** @var string the text before the username when logged. */
    public $helloText = null;
    
    /** @var string the view to render.*/
    public $view = 'loginBox';
    
    /** 
     * @inheritdoc
     * */
    public function run()
    {
        $model = new \yiingine\modules\users\models\UserLogin(); //Creates an instance of that login form.
        
        // If the widget intercepted a POST HTTP request and logging in credentials were found..
        if(Yii::$app->user->isGuest && $model->load(Yii::$app->request->post()))
        {            
            // If the site is read only or accounts have been disabled.
            if(Yii::$app->getParameter('app.read_only') || Yii::$app->getParameter('yiingine.users.disable_user_accounts'))
            {
                $model->addError('username', 'Site is read only or accounts are disabled'); // Add an error to prevent validation.
            }
            
            if($model->login())
            {
                $controller = Yii::$app->controller;
                $module = $controller->module;
                
                // If the user is logging in while on the users module.
                if($module && $module->id == 'users' && in_array($controller->id, ['profile', 'register']))
                {
                    Yii::$app->response->redirect(['/']);
                    return;
                }
                
                /* Re render the current page. Even if it could feel logical
                 * to just carry on with the rendering of the page, this should
                 * not be done because some code run earlier in the process might
                 * have needed to know if the user was logged in or not.*/
                Yii::$app->response->refresh();
            }
        }
        
        return $this->render($this->view, [
            'model' => $model,
            'switchType' => $this->switchType,
        ]);
    }
}
