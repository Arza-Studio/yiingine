<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\web;

use \Yii;

/**
 * A base controller for modules that depend on the media module.  
 * */
abstract class Controller extends \yiingine\web\SiteController
{        
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        //Set cache dependencies.
        $this->cacheDependencies = [\yiingine\modules\media\models\Medium::className(), \yiingine\modules\customFields\models\CustomField::className()];
        $this->cachingLevel = \yiingine\base\CacheControlInterface::CACHE_ALL; // Enable caching.
         
        // If the Page model can set views.
        if($this->module->enableModuleModel && $this->module->getModuleModel()->getViews())
        {
            //Use this view as a layout.
            $this->layout = $this->module->getModuleModel()->view;
        }
        
        parent::init();
    }
    
    /** 
     * Run the before_render script. before_render is a custom field that allows a model
     * to define special variables or to execute routines before it gets rendered. 
     * @param Medium $model the model that owns the script.
     * @return array $name => $value variables defined during the execution of the script, these
     * variables are meant to be passed to render().*/
    public function runBeforeRender($model)
    {
        //If a before_render script is defined, eval it to generate variables.
        return isset($model->before_render) && $model->before_render ? eval($model->before_render) : [];
    }
    
    /** Run the after_render script. after_render is a special custom field that allows a model
     * to execute routines after it gets rendered. 
     * @param Medium $model the model that owns the script.*/
    public function runAfterRender($model)
    {
        if(isset($model->after_render))
        {
            eval($model->after_render);
        }
    }
    
    /** 
     * @inheritdoc
     * */
    public function beforeAction($action)
    {
        /* Override of parent implementation to set meta_description and meta_keywods
         * with the data contained in the module model.*/
        
        // If there is a model for this module.
        if($model = $this->module->getModuleModel())
        {
            if(!$model->getEnabled()) //If the module has been disabled.
            {
                 //If the user is a guest or not a superuser and the user has the authorization to view the module.
                if(Yii::$app->user->isGuest || !Yii::$app->user->getIdentity()->superuser || !Yii::$app->getModule($model->module_owner_id)->checkAccess())
                {
                    throw new \yii\web\NotFoundHttpException(); // Hide the module from the client.
                }
            }
            
            // If the social metas component is present.
            if(Yii::$app->has('socialMetas'))
            {
                $socialMetas = Yii::$app->get('socialMetas');
                
                //If this module model has a meta description.
                if(isset($model->description) && ($description = $model->description))
                {
                    $socialMetas->description = $description;
                }
                //If this module model has meta keywords.
                if(isset($model->keywords) && ($keywords = $model->keywords))
                {
                    $socialMetas->keywords = $keywords;
                }
                //If this module model has a thumbnail.
                if($thumbnail = $model->getThumbnail())
                {
                    $socialMetas->thumbnail = $thumbnail;
                }
            }
        }
        
        return parent::beforeAction($action);
    } 
}
