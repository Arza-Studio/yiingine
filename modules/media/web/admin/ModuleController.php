<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\web\admin;

/**
 * A base controller for module models.
 * */
abstract class ModuleController extends MediumController
{
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        $this->singleton = true;
    }
    
    /**
    * @inheritdoc
    */
    public function accessRules()
    {
        $access = ucfirst($this->module->id).'Module-Page';
        
        return array_merge(
            [
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'verbs' => ['get'],
                    'roles' => [$access.'-view', 'Module-Page-view']
                ],
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'roles' => [$access.'-view', 'Module-Page-update']
                ]
            ], parent::accessRules()
        );
        
    }
    
    /**
     * @inheritdoc
     */
    public function model()
    {
        return new $this->module->moduleModelClass();
    }
    
    /**
     * @inheritdoc
     */
    public function loadModel($id)
    {
        /* Override of parent implementation to always return the module model
         * because we are in singleton mode.*/
        
        $model = $this->module->getModuleModel(true);
        $model->autoTranslate = false;  // Turn off automatic translation of attributes.
        return $model;
    }
    
    /** 
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /* Override of parent implementation to to deny access to the controller
         * if the module model is disabled.*/
        
        if(!$this->module->enableModuleModel)
        {
            throw new \yii\web\NotFoundHttpException();   
        }
        
        return parent::beforeAction($action);
    }
}
