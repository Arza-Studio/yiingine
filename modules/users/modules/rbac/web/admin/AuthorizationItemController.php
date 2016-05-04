<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\web\admin;

use Yii;
use \yiingine\modules\users\modules\rbac\models;

/** 
 * An admin controller for AuthorizationItems. 
 * */
abstract class AuthorizationItemController extends AuthorizationObjectController
{
    /** 
     * Initializes the controller.
     * */
    public function init()
    {
        parent::init();
        
        // Use a generic index view for all authorization items.
        $this->indexView = '/admin/authorizationItem/index';
    }
    
    /**
    * Gets an instance of the model this controller is managing.
    * @return Model an instance of the model.
    */
    public function model()
    {
        $class = substr($this::className(), strrpos($this::className(), '\\') + 1);
        
        // Determine the type from the name of the controller.
        switch(substr($class, 0, strlen($class) - 10))
        {
            case 'Role': return new models\Role();
            case 'Permission': return new models\Permission();
            default: throw new \yii\base\Exception('Unknow authorization item class.');
        }
    }
    
    /**
     * Returns the data model based on an identifier given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * A controller for a singleton model should override this method
     * to always provide the singleton model.
     * @param string the name of the item to be loaded
     * @throws \yii\base\HttpException
     */
    public function loadModel($id)
    {                
        // If an item  with that id was not found.
        if(!$item = Yii::$app->authManager->getItem($id))
        {
            throw new \yii\web\NotFoundHttpException(); // Authorization Item was not found.
        }    
        
        $model = $this->model();
        
        return new $model($item); //Returns the model.
    }
    
    /**
    * Deletes a particular model. Override of parent implementation to prevent deletion of
    * the Administrator role.
    * If deletion is successful, the browser will be redirected to the 'admin' page.
    * @param integer $id the ID of the model to be deleted
    * @throws CHttpException
    */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        
        if($model->name == 'Administrator')
        {
            throw new \yii\web\ForbiddenHttpException();
        }
        
        return parent::actionDelete($id);
    }
    
    /** @param CActiveRecord $model the model to get the form from.
     * @param array $structure the form structure, will be automatically fetched if not provided.
     * @return mixed the form objects for the model this controller manages.*/
    /*public function getForm($model, $structure = array())
    {
        // The form files for authorization items are found using the name of the controller.
        return parent::getForm($model, require $this->getViewFile('_forms/_'.lcfirst(str_replace('Controller', '', get_class($this)))));
    }*/
}
