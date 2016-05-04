<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\behaviors;

use \Yii;

/**
 * Restrict users from making certain requests to the site if it is
 * read-only or if user accounts are disabled.
 */
class ApplicationBlockBehavior extends \yii\base\Object
{
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        // Register this object to handle the before request event.
        Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        
        parent::init();
    }
    
    public static $_called = 0;
    
    /**
     * Load configuration from the database generate it at runtime.
     * @param Event $event the event that triggered the handler.
     */
    public function beforeRequest($event)
    {         
        if(self::$_called > 0) dump();

        self::$_called++;
        # BLOCK REQUESTS WITH SIDE EFFECT IF SITE IS READ-ONLY
        
        /* If the request can have a side effect (not HEAD or GET), if site
         * is in read-only mode and the currenlty logged in user is
         * allowed to bypass this mechanism.*/
        if( Yii::$app->getParameter('app.read_only'))
        {
            // Inform users that the site is read only.
            Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::WARNING, ['message' => Yii::t('site', 'WARNING ! Site is read-only for planned maintenance.')]);
            
            if(Yii::$app->request->method != 'GET' &&
                Yii::$app->request->method != 'HEAD' &&
                !$this->_isUserAllowed() &&
                !$this->_isRoute(['users/default/logout']) // At least let users logout.
            )
            {
                /* IMPORTANT! If a code processes POST requests and is displayed on the error
                 * page, the request will be processed anyway. It is the responsibility of this code
                 * to verify that the site is not in read only mode.
                 */
                throw new \yii\web\HttpException(503); // Service unavailable.
            }
        }
        
        # LOGOUT USERS IF LOGIN IS DISABLED
        
        /* If user accounts have been temporarily disabled log all users out except
         * for the users that are allowed to bypass this mechanism. */
        if(Yii::$app->getParameter('yiingine.users.disable_user_accounts') && 
            !Yii::$app->user->isGuest && 
            !$this->_isUserAllowed()
        )
        {
            Yii::$app->user->logout();
            throw new \yii\web\HttpException(503); // Service unavailable.
        }
        
        /* Redirects everyone to a maintenance page but the administrators 
         * when yiingine is in emergency maintenance mode.*/
        if(Yii::$app->getParameter('app.emergency_maintenance_mode.enabled'))
        {
            if(!$this->_isUserAllowed() &&
                !$this->_isRoute([
                'users/default/admin-login',
                'site/maintenance',
                'admin/default/index'
            ]))
            {
                Yii::$app->response->redirect(['/site/maintenance']);
            }
            
            // Inform users that the site in maintenance mode.
            Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::WARNING, ['message' => Yii::t('site', 'WARNING ! Site is in maintenance mode.')]);
        }
    }
    
    /** 
     * @return boolean if the current logged in user can bypass blocking.
     * */
    private function _isUserAllowed()
    {
        $user = Yii::$app->user;
        
        return !$user->isGuest && (
            in_array($user->getIdentity()->username, Yii::$app->params['app.special_users']) ||
            $user->can('YiingineBlockBypass') ||
            $user->can('Administrator')
        );
    }
    
    /** 
     * @param array $routes the routes to check.
     * @return boolean if the current route is part of the routes.
     * */
    private function _isRoute($routes)
    {
        return in_array(Yii::$app->urlManager->parseRequest(Yii::$app->request)[0], $routes);
    }
}
