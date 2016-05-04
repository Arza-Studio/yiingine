<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\base;

use yiingine\base\CacheControlInterface;
use yiingine\modules\media as media;
use \Yii;

/**
 * A base class that implements IMediaModule.
 */
abstract class Module extends \yiingine\base\Module implements ModuleInterface, CacheControlInterface
{            
    /** @var Medium the module's model.*/
    private $_moduleModel = null;
    
    /** @var integer the amount of seconds data should remain in cache.
     * @see COutputCache.*/
    public $cacheDuration = 8640000; //One hundred days.
    
    /** @var integer the level of caching for this module. Either 0 for none, 1 for views only
     * and 2 for whole pages. */
    public $cachingLevel = CacheControlInterface::CACHE_ALL;
    
    /** @var boolean if this module should have a module model.*/
    public $enableModuleModel = true;
    
    /** @var array a list of models this module can use as media;*/
    public $mediaClasses;
    
    /** @var string the module model's class. */
    public $moduleModelClass;
    
    /**
     * @inheritdoc
     * */
    public function getModuleModel($refresh = false)
    {
        if(!$this->enableModuleModel)
        {
            return null;
        }
        
        if(!$this->_moduleModel) //If no module model has been defined yet.
        {
            //Attempt to retrieve it from cache.
            if($refresh || (!$model = Yii::$app->cache->get($this->id.'ModuleModel')))
            {
                $class = $this->moduleModelClass;
                
                //The model was not found in cache, attempt to fetch it from the database.
                if(!$model = $class::find()->where(['module_owner_id' => $this->id])->one())
                {
                    //Model is not in database, create it.
                    $model = new $class();
                    $model->module_owner_id = $this->id;
                    
                    $currentLanguage = Yii::$app->language; //Save the current language.
                    foreach(Yii::$app->params['app.supported_languages'] as $language)
                    {
                        Yii::$app->language = $language;
                        $model->setAttribute('page_title', \yii\helpers\Html::tag('h1', $this->getLabel()), $language);
                    }
                    Yii::$app->language = $currentLanguage; //Restore the current language.
                    
                    if(!$model->save()) //If validation failed.
                    {
                        throw new \yii\base\Exception('Saving of model for module '.$this->id.' failed because of validation error "'.$model->getFirstError().'". Please refrain from using required fields for media of type Page.');
                    }
                }
                
                $model->module = $this->id; //Reset the module in case this was a refresh.
                
                // Save the model in cache.
                //Yii::$app->cache->set($this->id.'ModuleModel', $model, 0, new \yiingine\caching\GroupCacheDependency(['Medium', 'CustomField']));
            }
            
            $this->_moduleModel = $model;
        }
        
        return $this->_moduleModel;
    }
    
    /** 
     * @inheritdoc
     * */
    public function checkAccess()
    {        
        return ($this->enableModuleModel && (Yii::$app->user->can(ucfirst($this->id).'Module-Page-view') || Yii::$app->user->can('Module-Page-view'))) || parent::checkAccess();
    }
    
    /**
     *  @inheritdoc
     * */
    public function getCacheDuration()
    {
        return $this->cacheDuration;
    }
    
    /**
     * @inheritdoc
     * */
    public function getCachingLevel()
    {
        return $this->cachingLevel;
    }
}
