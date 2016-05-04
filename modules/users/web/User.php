<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\web;

use \Yii;

/**
 * This class overrides Yii's User to define functionnalities for the engine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class User extends \yii\web\User
{   
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        if(Yii::$app->request->get('confirmLogout'))
        {
            // Inform the user he has logged out sucessfully.
            Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::SUCCESS, Yii::t(__CLASS__, 'You have sucessfully logged out.'));
            unset($_GET['confirmLogout']);
        }
    }
    
    /**
     * Override of parent implementation to grant all access
     * to the 'admin' and 'administrator' users.
     * @inheritdoc
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        // These users can do everything with the engine.
        if((!$this->isGuest && in_array($this->getIdentity()->username, Yii::$app->params['app.special_users'])) ||
             (Yii::$app->getParameter('enable_auth_management') &&
             Yii::$app->authManager->getAssignment('Administrator', $this->id)
         ))
        {
            return true;
        }
        
        // If auth management is disabled.
        if(!Yii::$app->getParameter('enable_auth_management'))
        {
            return !$this->isGuest && $this->getIdentity()->superuser; // Super users can access everything.
        }
        
        return parent::can($permissionName, $params, $allowCaching);
    }
}
