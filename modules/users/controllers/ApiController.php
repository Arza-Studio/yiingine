<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\controllers;

use \Yii;

/**
* The API controller for the users module.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class ApiController extends \yiingine\web\ApiController
{                
    /**
    * @inheritdoc
    */
    public function accessRules()
    {
        return array_merge([
               [
                'allow' => true, // Everyone can see the login ang logout actions.
                'actions' => ['login', 'logout'],
            ],
        ], parent::accessRules());
    }
    
    /**
     * Action for logging in the API. 
     * */
    public function actionLogin()
    {
        if(!Yii::$app->user->isGuest) // If user is already registered.
        {
            throw new \yii\web\UnauthorizedHttpException();
        }
        else if(!Yii::$app->request->isPost) // Only POST requests are allowed.
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }
        
        $model = new \yiingine\modules\users\models\UserLogin();
        
        $model->attributes = $_POST;
        if(!$model->login()) //Validate the user's credentials.
        {
            //Always return the same error to prevent hackers from knowing they got part of the credentials right.
            throw new \yii\web\UnauthorizedHttpException(Yii::t(__CLASS__, 'Username or password incorrect.'));
        }
        
        return Yii::t(__CLASS__, 'You have sucessfully logged in.');
    }
    
    /**
     * Action for logging out.
     * */
    public function actionLogout()
    {
        if(Yii::$app->user->isGuest) // If user is not logged in.
        {
            throw new \yii\web\UnauthorizedHttpException();
        }
        else if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }
        
        Yii::$app->user->logout();
        
        return Yii::t(__CLASS__, 'You have sucessfully logged out.');
    }
}
