<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\customFields\controllers\admin;

use \Yii;
use \yiingine\modules\customFields\models\CustomField;

/** 
 * The admin controller for the CustomField model.
 * */
class CustomFieldController extends \yiingine\web\admin\ModelController 
{
    /* 
     * *@var CustomField the loaded model if there is one.
     * */
    private $_loadedModel = null;
    
    public function init()
    {
        parent::init();
        
        //CustomField::$module = $this->module;
    }
    
    /**
     * @inheritdoc
     */
    public function model()
    {
        $request = Yii::$app->request;
        
        // If the request is POST, we might be able to deduce the type.
        if($request->isPost)
        {
            // If the default model is being used.
            if($request->post('CustomField'))
            {
                // Get the types supported by the module.
                $type = $this->module->factory->getTypes();
                if(isset($type[$request->post('CustomField')['type']]))
                {
                    // Return an instance of the class specified by the given type.
                    $type = $type[$request->post('CustomField')['type']];
                    
                    /* Copy the data over to the new type name so
                    it can be used to get the attributes to set.*/   
                    $_POST[$type] = $request->post('CustomField');
                    
                    return new $type($this->module);
                }
                else
                {
                    return new CustomField($this->module);
                }
            }
            else if(!$request->post()) // If no data was provided.
            {
                return new CustomField($this->module); //Use the generic type.
            }
            else
            {
                $types = $this->module->factory->getTypes();
                
                foreach($types as $class)
                {
                    $model = new $class($this->module);
                    
                    // If the request contained a type.
                    if(isset($request->post($model->formName())['type']))
                    {   
                        // If the type does not exist.
                        if(!in_array($request->post($model->formName())['type'], $types))
                        {
                            throw new \yii\web\BadRequestHttpException('Type does not exist.');
                        }
                        
                        $class = $request->post($model->formName())['type'];
                        
                        if(isset($request->post($model->formName())['configuration'])) // If a configuration was set.
                        {
                            // If it is the default configuration.
                            if($model->getExampleConfiguration() == $request->post($model->formName())['configuration'])
                            {
                                // Let the new model specify its configuration.
                                unset($request->post($model->formName())['configuration']);
                            }
                        }
                        
                        /*Copy the data over to the new type name so
                         it can be used to get the attributes to set.*/
                        $_POST[$class::formName()] = $request->post($model->formName());
                        
                        return new $class($this->module);
                    }    
                }
            }
            
            // The type was not found.
            throw new \yii\web\BadRequestHttpException('Type not found.');
        }
        else if($this->_loadedModel) //If a model has been loaded previously.
        {
            // Return a model of the same class.
            $class = get_class($this->_loadedModel);
            return new $class($this->module);
        }
        else // Use the generic type.
        {
            return new CustomField($this->module);
        }
    }
    
    /** 
     * @inheritdoc 
     * */
    public function getAccessRulePrefix()
    {
        // Override of parent implementation because customfields are used as a child module.
        return parent::getAccessRulePrefix().'-'.$this->module->module->id;
    }
    
    /**
     * @inheritdoc
     */
    public function loadModel($id)
    {
        /* Override of parent implementation to make a database query with a model
         * different from the one stored in the database.*/
        
        $id = (int)$id;
        if(!is_integer($id)) //$id is a primary key so it must be an integer.
        {
            throw new \yii\web\BadRequestHttpException();
        }
        
        /* Instantiates that class and do a search by primary key on it.
         * Do not use a strict query in case the type of field has been changed and we need to
         * retrieve a row of the table from under a different type it was originally saved in.*/
        if(!$model = $this->model()->find($this->module, false)->where(['id' => $id])->one())
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
     * @inheritdoc
     * */
    public function getFormStructure($model)
    {
        // Override of parent implementation because all subtypes of CustomField use the same form.
        return $this->requireFile('_forms/_customField', ['model' => $model], $this);
    }
    
    /**
     * @inheritdoc
     */
    public function actionCreate($copy = null)
    {
        // Override of parent implementation to get a new form depending on the state of some fields.
        
        if(Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            $model = $this->model();  //Use a blank model.
            
            foreach(Yii::$app->request->post() as $name => $data)
            {
                if(strpos($name, 'Field') !== false) // Get the form data from the previous field.
                {
                    $model->attributes = $data;
                    break;
                }
            }
            
            return $this->renderPartial($this->createView, ['model' => $model, 'form' => $this->getFormStructure($model)]);
        }
        
        return parent::actionCreate($copy); // Proceed normally.
    }
    
    /**
    * @inheritdoc.
    */
    public function actionDelete($id)
    {
        // Override of parent implementation to prevent deletion of protected models.
        
        $model = $this->loadModel($id);
        
        if($model->protected) // If this model is protected.
        {
            throw new \yii\web\ForbiddenHttpException('Protected fields cannot be deleted.');
        }
        
        return parent::actionDelete($id);
    }
    
    /**
     * @inheritdoc
     */
    public function actionUpdate($id)
    {
        // Override of parent implementation to get a new form depending on the state of some fields.
        
        if(Yii::$app->request->isAjax && Yii::$app->request->isPost)
        {
            $model = $this->loadModel($id);
            
            foreach(Yii::$app->request->post() as $name => $data) // Get the form data from the previous field.
            {
                if(strpos($name, 'Field') !== false)
                {
                    $model->attributes = $data;
                    break;
                }
            }
            
            return $this->renderPartial($this->updateView, ['model' => $model, 'form' => $this->getFormStructure($model)]);
        }
        
        return parent::actionUpdate($id);
    }
}
