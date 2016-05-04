<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web\admin;

use \Yii;

/**
 * This class is a generic model management controller for the 
 * admin portion of the yiingine. 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class ModelController extends Controller
{
    /** @var string the name of the index view.*/
    protected $indexView = 'index';
    
    /** @var string the path to the update view.*/
    protected $updateView = '//admin/model/update';
    
    /** @var string the path to the "create" view.*/
    protected $createView = '//admin/model/create';
    
    /** @var string the path to the "view" view.*/
    protected $viewView = '//admin/model/view';
    
    /**
     * Whether or not the model managed by this controller can be created.
     * For example, this controller could be managing an abstract model,
     * which can be edited and deleted but not created. Abstract is used here 
     * for databse inheritance rather that PHP inheritance since by design,
     * models managed by Yii's ActiveRecord class can not be abstract.
     * @var boolean
     */
    public $allowCreate = true;
    
    /** @var boolean allows the user to copy a model.*/
    public $allowCopy = true;
    
    /** @var boolean the model managed by this controller is a singleton. A Singleton is a model
     * of which there can only exist one copy. */
    public $singleton = false;
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return \yiingine\widgets\admin\PositionManager::actions();
    }
    
    /**
    * Gets an instance of the model this controller is managing.
    * @return Model an instance of the model.
    */
    public function model()
    {
        // The controller is often named after the class so get the name from there.
        $class = str_replace('Controller', '', get_class($this));
        $class = str_replace('controllers', 'models', $class);
        
        if(!class_exists($class))
        {               
            // Maybe the model is in the parent directory's model folder.
            $class = str_replace('\\admin', '', $class);
        }
        
        return new $class();
    }
    
    /**
    * @inheritdoc
    */
    public function accessRules()
    {
        $prefix = $this->model()->className();
        
        $rules = [
            [
                'allow' => true,
                'actions' => ['view', 'update', 'index', 'create', 'positionManager.getLastAvailableValue', 'positionManager.moveValue'],
                'roles' => [$prefix.'-manage'],
            ],
            [ // Users with view role can also see a form, but not submit it.
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET'],
                'roles' => [$prefix.'-view'],
            ],
            [
                'allow' => true,
                'actions' => ['view', 'index'],
                'roles' => [$prefix.'-view'],
            ],
            [
                'allow' => true,
                'actions' => ['update', 'positionManager.getLastAvailableValue', 'positionManager.moveValue'],
                'roles' => [$prefix.'-update'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'roles' => [$prefix.'-delete'],
            ],
            [
                'allow' => true,
                'actions' => ['create', 'nextValue'],
                'roles' => [$prefix.'-create'],
            ],
            
        ];
        
        if(!$this->allowCreate) // If this model cannot be created.
        {
            $rules[] = ['allow' => false, 'actions' => ['create']];
        }
        
        /*This access list is merged before the parent's acccess list so
         * those permissions are checked first. This access list defines a role
         * of maintain and a username of maintainer for model maintenance.
         * It also defines specific roles for specific tasks.*/
        return array_merge($rules, parent::accessRules());
    }    
    
    /**
    * Displays a particular model.
    * @param integer $id the ID of the model to be displayed
    */
    public function actionView($id)
    {
        $model = $this->loadModel($id);
        return $this->render($this->viewView, array(
            'model' => $model,
            'form' => $this->getFormStructure($model)
        ));
    }
    
    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * A controller for a singleton model should override this method
     * to always provide the singleton model.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $id = (int)$id;
        if(!is_integer($id)) //$id is a primary key so it must be an integer.
        {
            throw new \yii\web\BadRequestHttpException();
        }
        
        // Instantiates that class and do a search by primary key on it.
        if(!$model = $this->model()->findOne($id))
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        if(isset($model->autoTranslate))
        {
            $model->autoTranslate = false; // Turn off automatic translation of attributes.
        }
        
        return $model;
    }
    
    /**
     * Creates a new model.
     * @param integer $copy the id of the model to copy, leave null if a blank model should be used.
     */
    public function actionCreate($copy = null)
    {
        // If we are allowed to create the model. Could be expressed as an accessRule.
        if(!$this->allowCreate)
        {
            //If not, throw a 403 exception.
            throw new \yii\web\ForbiddenHttpException(Yii::t(__CLASS__, 'The model managed by this resource does not support creation.'));
        }
        
        if($copy) //If we are copying an existing model.
        {
            if($this->singleton)
            {
                throw new \yii\web\ForbiddenHttpException('Singleton models cannot be copied!'); //Forbidden, cannot copy singleton models.
            }
            
            $model = clone $this->loadModel($copy);
            
            /* Since we do not want the copy parameter to be used for urls down the road, remove, it gets removed
             * manually.*/
            unset($_GET['copy']);
            unset($_POST['copy']);
        }
        else
        {
            $model = $this->model();
        }
        
        // If the HTTP request was a POST and it contained the model's data.
        if($model->load(Yii::$app->request->post()))
        {
            // Save the model; validation is done there.
            if($model->save())
            {
                if(Yii::$app->request->isAjax)
                {
                    Yii::$app->response->headers->add('Content-Type', 'application/json');
                    return CJSON::encode(array('id' => $model->id, 'class' => get_class($model))); // Return JSON data to indicate success.
                }
                else
                {
                    $this->redirectToConfirmSave($model, Yii::$app->request->post('redirectionActionOnSuccess'));
                    return;
                }
            }
        }
        
        // Renders the create view with the model.
        if(Yii::$app->request->isAjax || Yii::$app->request->get('ajaxModify') || Yii::$app->request->post('ajaxModify'))
        {
            $this->layout = '//layouts/adminFormOnly';
            if($model->hasErrors()) //If there are validation errors.
            {
                Yii::$app->response->exitStatus = 400; // Inform the client with an http status code (more restful).
            }
            
            return $this->render($this->createView, ['model' => $model, 'form' => $this->getFormStructure($model)]);
        }
        else
        {
            return $this->render($this->createView, ['model' => $model, 'form' => $this->getFormStructure($model)]);
        }
    }
    
    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {        
        $model = $this->loadModel($id); //Loads the model with that id.
        
        // If the HTTP request was a POST and it contained the model's data.
        if($model->load(Yii::$app->request->post()))
        {            
            // Save the model; validation is done there.
            if($model->save())
            {
                if(Yii::$app->request->isAjax)
                {
                    Yii::$app->response->headers->add('Content-Type', 'application/json');
                    
                    return \yii\helpers\Json::encode(['id' => $model->id, 'class' => get_class($model)]); // Return JSON data to indicate success.
                }
                
                $this->redirectToConfirmSave($model, Yii::$app->request->post('redirectionActionOnSuccess'));
                return;
            }
        }
        
        // Renders the update view with the model.
        if(Yii::$app->request->isAjax || Yii::$app->request->get('ajaxModify') || Yii::$app->request->post('ajaxModify'))
        {
            $this->layout = '//layouts/adminFormOnly';
            if($model->hasErrors()) //If there are validation errors.
            {
                Yii::$app->response->exitStatus = 400; //Inform the client with an http status code (more restful).
            }
            
            return $this->render($this->updateView, ['model' => $model, 'form' => $this->getFormStructure($model)]);
        }
        else // The request is not ajax.
        {
            return $this->render($this->updateView, ['model' => $model, 'form' => $this->getFormStructure($model)]);
        }
    }
    
    /**
    * Deletes a particular model.
    * If deletion is successful, the browser will be redirected to the 'admin' page.
    * @param integer $id the ID of the model to be deleted
    */
    public function actionDelete($id)
    {
        if($this->singleton) // Singletons cannot be deleted this way.
        {
            throw new \yii\web\ForbiddenHttpException(); 
        }
        
        // If the HTTP request was a POST. Deletion is only allowed via POST.
        if(Yii::$app->request->isPost)
        {
            $model = $this->loadModel($id);
            
            $model->delete();
            
            // If AJAX request (triggered by deletion via admin grid view), we should not redirect the browser.
            if(!Yii::$app->request->isAjax)
            {
                // Redirects to a specified return URL or the admin view.
                $this->redirect(Yii::$app->request->get('returnUrl') ? Yii::$app->request->get('returnUrl') : ['index']);
            }
        }
        else
        {            
            throw new \yii\web\MethodNotAllowedHttpException();
        }
    }
    
    /**
    * Manages all models.
    */
    public function actionIndex()
    {        
        if($this->singleton) // If the model is singleton, this action is used for edition.
        {
            if($model = $this->loadModel(null)) // If a model already exists.
            {
                return $this->actionUpdate($model->id); // Use the update action.
            }
             
            return $this->actionCreate(); // Else use the create action.
        }
        
        return $this->render($this->indexView, ['model' => $this->model()]);
    }
    
    /** 
     * @param ActiveRecord $model the model to get the form from.
     * @param array $structure the form structure, will be automatically fetched if not provided.
     * @return mixed the form objects for the model this controller manages.
     * */
    public function getFormStructure($model)
    {
        return $this->requireFile('_forms/_'.lcfirst($model->formName()), ['model' => $model], $this);
    }
    
    /**
     * Redirects the browser to the confirm save state, in which the user the user is 
     * shown the index page with a zoombox containing a view of the model and a
     * confirmation message.
     * @param ActiveRecord $model the model that was just saved.
     * @param string $redirect the url type to redirect to, defaults to the index.
     * */
    protected function redirectToConfirmSave($model, $redirect = 'index')
    {
        /* When redirecting, Yii does not run the afterAction event for the 
         * action that called the redirect. Therefore, the mutex does not get unlocked
         * there so we have to do it here.*/        
        
        if(Yii::$app->request->isAjax)
        {
            return; // Do not redirect if request was ajax.
        }
        
        $noReturnToFormButton = false;
        $noViewInSiteButton = false;
        
        switch($redirect)
        {
            case 'copy':
                $url = ['create', 'copy' => $model->id];
                $noViewInSiteButton = true;
                break;
            case 'form':
                $url = Yii::$app->request->queryParams; // Keep query parameters.
                $url[0] = 'update';
                $url['id'] = $model->id;
                $noReturnToFormButton = true;
                break;
            case 'overlay':
                /* If a referring url was passed to the form. This could happen for instance
                 * if the user clicked a model wrapped by an AdminOverlay widget. */
                if(Yii::$app->request->get('returnUrl'))
                {
                    $url = urldecode(Yii::$app->request->get('returnUrl'));
                    $noViewInSiteButton = true;
                    break;
                }
                // No break here.
            case 'site':
                // If the model can be viewed in the site.
                if($model instanceof \yiingine\db\ViewableInterface && $model->getUrl() !== false)
                {
                    $url = $model->getUrl();
                    $noViewInSiteButton = true;
                    break;
                }
                // No break here.
            default:
                $url = Yii::$app->request->queryParams;
                unset($url['id']);
                if($this->action->id == 'create')
                {
                    unset($url[$model->formName()]); // Remove the search parameters.
                    unset($url['sort']); // Remove the sort parameters.
                }
                $url[0] = 'index';    
        }
        
        // If the model is a singleton, its form is the index (only on admin side).
        if($this->singleton && $redirect != 'site') 
        {
            $noReturnToFormButton = true;
        }
        
        // Set a flash message to confirm the saving of the model.
        $this->renderPartial('//admin/model/_confirmSave', [
            'model' => $model,
            'noReturnToFormButton' => $noReturnToFormButton, 
            'noViewInSiteButton' => $noViewInSiteButton
        ]);
        
        $this->redirect($url);
    }
}
