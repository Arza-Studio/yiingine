<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\controllers\admin;

use \Yii;

/**
* @desc The main controller for the admin part of the Yiingine.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class DefaultController extends \yiingine\web\admin\Controller
{
    /**
    * Specifies the access control rules.
    * The result of this method is passed to the AccessControl filter.
    * @return array access control rules
    */
    public function accessRules()
    {
        $rules = [
            [ // Only an administrator can modify the site parameters.
                'allow' => true,
                'actions' => ['site'],
                'roles' => ['Administrator']
            ],
            [
                'allow' => true,
                'actions' => ['tasks'],
                'roles' => ['Tasks-manage']
            ],
            [ // All superusers can do the following actions.
                'allow' => true, 
                'actions' => ['index', 'flushCache', 'preferences', 'palette', 'ping'],
                'matchCallback' => function($rule, $action)
                { 
                    return Yii::$app->user->getIdentity() && Yii::$app->user->getIdentity()->superuser; 
                },
            ],
            [
                'allow' => true,
                'actions' => ['site'],
                'roles' => ['SiteConfiguration-update']
            ],
            [ // Any user is allowed to see the error page.
                'allow' => true,
                'actions' => ['error'], 
            ],
        ];
        
        return array_merge($rules, parent::accessRules());
    }
    
    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {                
        return $this->render('index'); // Renders index.
    }
    
    /**
     * This action presents the user with a list of tasks and offers him
     * to run them.
     */
    public function actionTasks()
    {
        $tasks = \yiingine\tasks\Task::getTasks(); // Gets all the task objects.
        
        if(Yii::$app->request->post('runTasks')) // If the request was a post and it contained "runTasks".
        {
            foreach($tasks as $task) // Iterates through all tasks.
            {
               $task->run(); // Run that task.
            }
        }
        
        return $this->render('tasks', [
            'tasks' => $tasks, 
            'model' => new \yiingine\models\TaskReport(['scenario' => 'search'])
        ]);
    }
    
    /**
    * The preferences action.
    */
    public function actionPreferences()
    {
        $model = new \yiingine\models\admin\AdminParameters();
        
        // If the request was a POST and if contained data for AdminParameters.
        if(Yii::$app->request->post('AdminParameters'))
        {
            $model->load(Yii::$app->request->post());
            
            if($model->validate())
            {
                $model->apply();
                Yii::$app->session->setFlash(\yiingine\widgets\FlashMessage::SUCCESS, \yii\helpers\Html::tag('div', Yii::t(__CLASS__, 'Parameters were saved sucessfully.'), ['class' => 'message']));
                $this->refresh(); //Some changes require the page to be refreshed.
                return;
            }
        }
        
        // Add an update button on the centerButtons.
        $this->actionBar->centerButtons[] = [
            'text' => Yii::t('generic', 'Update'),
            'id' => $model->formName().'-submit',
            'class' => 'adminBtn',
            'type' => \yiingine\widgets\admin\ButtonBar::SCRIPT,
            'attr' => "window.onbeforeunload=null;$('#".$model->formName()."-form').submit();"
        ];
        
        return $this->render('preferences', ['model' => $model]);
    }
    
    /**
    * The site configuration action.
    */
    public function actionSiteConfiguration()
    {           
        $model = new \yiingine\models\admin\SiteConfiguration();
        
        // If the request was a POST and if contained data for the site configuration.
        if($model->load(Yii::$app->request->post()))
        {
            if($model->save()) // Also executes validation.
            {
                // Set a flash message to confirm the saving of the model.
                $this->renderPartial('//admin/model/_confirmSave', [
                        'model' => $model,
                        'noReturnToFormButton' => true, 
                        'noViewInSiteButton' => true
                    ]
                );
            }
        }
        
        return $this->render('siteConfiguration', ['model' => $model]);
    }
    
    /**
    * This is the action to handle external exceptions.
    */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        
        if(!$exception) // If this was called without and error.
        {
            throw new \yii\web\BadRequestHttpException();
        }
        
//         /* If that error was triggered by an AJAX request.*/
//         if(Yii::app()->request->isAjax)
//         {
//             /*Since we will not be rendering fully formed HTML, use
//              * text/plain as Content-type.*/
//             header('Content-type: text/plain', true);
//             /*Just render the error view partially to save processing time.
//              * An ajax handler is mostly concerned wit the HTTP status code of
//             * the error and not the text itself.*/
//             $this->renderPartial('//system/ajaxError', array('error' => $error));
//             return;
//         }
        
        switch($exception)
        {
            case 401: // Unauthorized.
            case 403: // If the access was forbidden.
                if(Yii::$app->user->isGuest) // If the user is not logged in.
                {
                    Yii::$app->user->returnUrl = Yii::$app->request->url;
                    Yii::$app->user->loginRequired(); // Offer to log in.
                    break;
                }
                // If the user is not a super user but is trying to access the admin.
                else if(!Yii::$app->user->getIdentity()->superuser)
                {
                    $this->forward('/site/error'); // Redirect to the error view for the site.
                }
                // Else display error.
            default:
                // If the user is not logged in or does not have access to the admin side of the site.
                if(Yii::$app->user->isGuest || !Yii::$app->user->getIdentity()->superuser)
                {
                    // Forward the exception to the site's error handler.
                    Yii::$app->errorHandler->errorAction = 'site/error';
                    Yii::$app->errorHandler->handleException($exception);
                    return;
                }
                return $this->render('//system/error', ['exception' => $exception]);
        }
}
    
    /**
     * Flushes the entire cache for a site.
     */
    public function actionFlushCache($redirect = 1)
    {
        // This action should be only accessible using a POST, but since
        // the site may be unusable before the cache has been flushed, an
        // exception is made.
        $this->clean();
        // If the redirecting is still enabled
        if($redirect)
        {
            if(Yii::app()->request->referrer) // If this action was acessed through a page.
            {
                $this->redirect(Yii::app()->request->referrer); 
            }

            $this->redirect(array('/site/index')); // Redirect to the home page.
        }
    }
        
    /**Renders a visual representation of the site's color palette.
     * @param string $color the color to render a swatch for, leave blank
     * for all colors.*/
    public function actionPalette($color = null)
    {    
        if(!$color) //If no color was specified.
        {
            //Render a swatch for all colors.
            $colors = array_keys(Yii::app()->palette->colors);
        }
        else
        {
            //If the requested color does not exist.
            if(!isset(Yii::app()->palette->colors[$color]))
            {
                throw new CHttpException(404); //Not found.
            }
            //Render a swatch for that specific color only.
            $colors = array($color);
        }
        
        $this->renderPartial('palette', array('colors' => $colors, 'palette' => Yii::app()->palette));
    }
    
    /**Renders a visual representation of the admin color palette.
     * @param string $color the color to render a swatch for, leave blank
     * for all colors.*/
    public function actionAdminPalette($color = null)
    {
        if(!$color) //If no color was specified.
        {
            //Render a swatch for all colors.
            $colors = array_keys(Yii::app()->adminPalette->colors);
        }
        else
        {
            //If the requested color does not exist.
            if(!isset(Yii::app()->adminPalette->colors[$color]))
            {
                throw new CHttpException(404); //Not found.
            }
            //Render a swatch for that specific color only.
            $colors = array($color);
        }
        
        $this->renderPartial('palette', array('colors' => $colors, 'palette' => Yii::app()->adminPalette ));
    }
}
