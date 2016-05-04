<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\base;

use \Yii;

/**
 * This class describes an abstract module for the Yiingine.
 * 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class Module extends \yii\base\Module implements \yii\base\ViewContextInterface
{
    /**
    * @var string the admin layout for this module. Empty string if there is none.
    */
    public $adminLayout = '';
    
    /** @var mixed the label of a module. If given as an array, it will be converted to a string using Yii::tA. */
    public $label;
    
    /** @var string|boolean the route to the module's sitemap or false if the module does have a sitemap.. */
    public $moduleMapRoute = '/default/module-map';
    
    /**
     * @inheritdoc.
     */
    public function init()
    {
        parent::init();
        
        if(!isset($this->label)) // If no label was provided.
        {
            $this->label = $this->id; // Use the module's name.
        }
    }
    
    /** 
     * @return string the label of the module.
     * */
    public function getLabel()
    { 
        return Yii::tA($this->label);
    }
    
    /**
     * Return an array of sub-modules of this modules instances.
     * @return array the instances.
     */
    public function getSubModules()
    {
        $modules = []; // The application's modules.
        
        // For each module in this module.
        foreach($this->modules as $k => $v)
        {
            // Create an instance and push it on $modules.
            $modules[] = $this->getModule($k);            
        }
        
        return $modules;
    }
    
    /** 
     * @return boolean if the current user can access this module.
     * */
    public function checkAccess()
    {
        foreach($this->getSubModules() as $module)
        {
            if($module->checkAccess()) //If ther user has access to a sub module.
            {
                return true; //The user can acess this module as well.
            }
        }
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function createControllerByID($id)
    {
        /* Override of parent implementation to check in the yiingine
         * if the controller cannot be found in the app. */
        
        if(!($controller = parent::createControllerById($id)))
        {
            $this->controllerNamespace = str_replace('app', 'yiingine', $this->controllerNamespace);
            $controller = parent::createControllerById($id);
        }
        
        return $controller;
    }
    

    /**
     * Provides an adminstration panel for the module for use within the admin's index page.
     * By default, this method checks for the presence of a view named _indexPanel.php in /views/admin.
     * 
     * @return string|boolean the html of the panel or false if none.
     * */
    public function getAdminPanel()
    {
        try
        {
            return Yii::$app->view->render('admin/_indexPanel.php', [], $this);
        }
        catch(\yii\base\InvalidParamException $e)
        {
            return false;
        }
    }
}
