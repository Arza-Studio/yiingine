<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web;

/**
 * Yii2's \yii\web\Application adapted for use with the yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class Application extends \yii\web\Application
{            
    /**
     * Returns the human adapted label for the module.
     * @return string the label
     */
    public function getLabel() { return ''; }
    
    /** 
     * @return boolean if the current user can access this module.
     * */
    public function checkAccess() { return true; } 
    
    /** 
     * Verifies that an application parameter exists and return its value.
     * @param string $name the name of the parameter.
     * @param mixed $default value to be returned if the configuration entry does not exit.
     * @return mixed the value of the parameter or false if it does not exist.
     * */
    public function getParameter($name, $default = false)
    {
        return isset($this->params[$name]) && !empty($this->params[$name]) ? $this->params[$name] : $default ;
    }
    
    /**
     * Clean every asset, cached file, etc. So they can be regenerated on the next request.
     */
    public function clean()
    {
        $this->cache->flush(); // Flushes the entire cache.
        
        // Remove all folders from the asset folder.
        foreach(scandir($this->assetManager->basePath) as $directory)
        {
            if($directory == '..' || $directory == '.')
            {
                continue;
            }
            
            \yii\helpers\FileHelper::removeDirectory($directory);
        }
        
        if($this->has('imagine'))
        {
            $this->imagine->flush(); // Flush all modified images.
        }
    }
    
    /**
     * @inheritdoc
     */
    public function createControllerByID($id)
    {
        /* Override of parent implementation to check in the yiingine
         * if the controller cannot be found in the app. */
        
        if(!$controller = parent::createControllerById($id))
        {
            $this->controllerNamespace = str_replace('app', 'yiingine', $this->controllerNamespace);
            $controller = parent::createControllerById($id);
        }
        
        return $controller;
    }
    
    /**
     * The first language in the supported_languages configuration entry defines
     * the language that is not translated (the one stored in the fields of the models)
     * unless the sourceLanguage is present within the supported languages array in which case it
     * becomes the language that is not translated.
     * @return the language that is not translated.
     * */
    public function getBaseLanguage()
    {
        return in_array($this->sourceLanguage, $this->params['app.supported_languages']) ?
        $this->sourceLanguage : $this->params['app.supported_languages'][0];
    }
}
