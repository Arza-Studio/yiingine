<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\web\admin;

use \Yii;

/**
 * Admin controller for media models. Most of the actions inherited from AdminModelController are
 * overriden because this controller needs to manage not one model but the medium itself, its fields
 * and its children media. Its behavior is modified by the "type" request argument. Without it
 * it manages Mediums in an abstract way while with it, it manages specific types. A type of medium
 * is defined by the fields it is allowed to use.
 * */
abstract class MediumController extends \yiingine\web\admin\ModelController
{   
    /** 
     * @inheritdoc 
     * */
    public function actions()
    {
        $actions = [];
        
        foreach($this->model()->getManagers() as $manager)
        {
            $actions = array_merge($manager->actions(), $actions);
        }
        
        return array_merge(parent::actions(), $actions);
    }
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        $class = $this->model()->className();
        $this->singleton = $class::$singleton;
        
        parent::init();
    }
    
    /**
     * @inheritdoc
     */
    public function loadModel($id)
    {
        // Overload of parent implementation to support singleton models.
        
        if($this->singleton) // Fetch the same model if a singleton is being managed.
        {
            if($model = $this->model()->find()->where(['type' => $this->model()->className()])->one())
            {
                $model->autoTranslate = false; // Turn off automatic translation of attributes.
            }
            
            return $model;
        }
        
        return parent::loadModel($id);
    }
    
    /** 
     * @inheritdoc
     * */
    public function getFormStructure($model)
    {
        $class = $model->formName();
        
        // Check all the paths that could contain a medium view in order of priority.
        foreach([
             '@app/modules/'.$this->module->id.'/views/admin/'.$class.'/_forms/_'.$class.'.php',
             '@yiingine/modules/'.$this->module->id.'/views/admin/'.$class.'/_forms/_'.$class.'.php',
             '@app/modules/media/views/admin/medium/_forms/_medium.php',
             '@yiingine/modules/media/views/_forms/_medium.php',
            ] as $file)
        {
            if(is_file(Yii::getAlias($file)))
            {
                return $this->requireFile($file, ['model' => $model], $this);
            }
        }
        
        throw new \yii\base\Exception('Could not find a form file for type '.$this->type);
    }
}
