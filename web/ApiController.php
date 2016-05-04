<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web;

use \Yii;

/**
 * This class describes a generic web API controller for the yiingine. 
 * Controllers managing an API should inherit from this class.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class ApiController extends Controller
{    
    /**
    * @inheritdoc
    */
    public function init()
    {
        $this->setSide(Controller::API); // Set the application side.
        
        //Set the parameters for the client filter.
        $this->updateClientRoute = ['/api/update-client'];
        $this->incompatibleClientsEntry = 'incompatible_api_clients';
        
        parent::init();
        
        // Set the default content type for the API.
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/plain');
        
        // Not needed for the API.
        Yii::$app->request->enableCsrfValidation = false;
        
        // Raise an HTTP 403 exception when login is required.
        Yii::$app->user->loginUrl = null;
        
        $this->layout = false; // The API does not use layouts by default.
    }
    
    /**
    * @inheritdoc
    */
    public function accessRules()
    {
        return [
               [
                'allow' => true, // Everyone can see the error action.
                'actions' => ['error'],
            ],
            [ 
                'allow' => true,
                'roles' => ['Administrator, APIUser'], // These roles are allowed to do anything with the API.
            ],
            [ // These users are granted everything.
                'allow' => true, 
                'matchCallback' => function($rule, $action){ return !Yii::$app->user->isGuest && in_array(UsersModule::user()->username ,Yii::app()->params['app.special_users']); }
            ],
            [
                'allow' => false,  // Deny all other users.
            ],
        ];
    }
}
