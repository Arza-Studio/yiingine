<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\controllers;

use \Yii;

/**
* @desc The main controller for the API part of the yiingine.
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
                'allow' => true,
                'actions' => array_keys(\yiingine\widgets\SessionMonitor::actions()),
                'matchCallback' => function($rule, $action){ return !Yii::$app->user->isGuest; }
            ],
               [
                'allow' => true,
                'actions' => ['index', 'hiddenText.show', 'updateClient', 'language', 'fileListUploader.upload'],
            ],
        ], parent::accessRules());
    }
    
    /** 
     * @inheritdoc
     * */
    public function actions()
    {
        return array_merge(
            parent::actions(),
            \yiingine\widgets\HiddenText::actions(),
            \yiingine\widgets\admin\FileListUploader::actions(),
            \yiingine\widgets\SessionMonitor::actions()
        ); 
    }
    
    /** 
     * The default action for the API.
     * */
    public function actionIndex()
    {        
        return $this->render('index');
    }
    
    /**
     * Returns a JSON array showing status information about the site and how it is used.
     * */
    public function actionStatus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $status = [
            'yingineVersion' => YIINGINE_VERSION,
            'yiiVersion' => Yii::getVersion(),
            'applicationName' => Yii::$app->name,
            'availableLanguages' => Yii::$app->params['app.available_languages'],
            'supportedLanguages' => Yii::$app->params['app.supported_languages'],
            'yiiEnv' => YII_ENV,
            'debugMode' => YII_DEBUG,
            'databaseLocation' => DB_LOCATION 
        ];
        
        /* Retrieve the name and date the last super user logged into the site. Since we have
         * to be logged in as an admin to use the API, then that user is the second one.
         */
        $lastAdminLogin = \yiingine\modules\users\models\User::find()->where(['superuser' => 1])->orderBy('lastvisit DESC')->limit(2)->all();
        if(isset($lastAdminLogin[1]))
        {
            $status['lastAdminLoginDate'] = $lastAdminLogin[1]->lastvisit;
            $status['lastAdminLoginName'] = $lastAdminLogin[1]->username;
        }
        
        // Lists the name of all installed modules.
        $modules = [];
        foreach(Yii::$app->modules as $name => $module)
        {
            $modules[] = $name;
        }
        $status['installedModules'] = $modules;
        
        return $status;
    }

    /** 
     * The action the user is redirected to when his client needs to be updated.
     * */
    public function actionUpdateClient()
    {
        if(Yii::$app->request->post('ignore')) //If the request was a POST with ignore set.
        {
            /*The user has opted to ignore the update client warning.
             * Set a cookie so he does not get bothered until the next time
             * he opens his client and access the site.*/
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'ignoreUpdateClient',
                'value' => 1 
            ]));
        }
        else
        {
            return Yii::t(__CLASS__, 'Incompatible client, please update your client to the most recent version.');
        }
    }
    
    /**
     * Switches between supported languages.
     */
    public function actionLanguage()
    { 
        if(Yii::$app->request->post('language')) //If the request is a POST and lang is set.
        {
            $language = Yii::$app->request->post('language'); // Gets the language.
            
            $previousLanguage = Yii::$app->language; //Save the previous language.
            
            // If $language is an available language.
            if(in_array($language, Yii::$app->params['app.available_languages']))
            {
                Yii::$app->language = $language;
            }
            else
            {
                throw new \yii\web\NotFoundHttpException(Yii::t(__CLASS__, 'Language not supported')); // Not found.
            }
            
            // Sets the language session variable.
            Yii::$app->session['language'] = Yii::$app->language;
            
            // Sets the language cookie to remeber the language between site accesses.
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'language',
                'value' => Yii::$app->language
            ]));
        }
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        // Return the current language.
        return Yii::$app->language;
    }
}
