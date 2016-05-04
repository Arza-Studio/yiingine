<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web\admin;

use \Yii;
use \yiingine\modules\users\UsersModule;
use \yiingine\models\admin\AdminParameters;

/**
 * This class describes a generic controller for the admin portion of 
 * the yiingine. The different security requirements between the admin
 * section and the user section motivated the creation of this file.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class Controller extends \yiingine\web\Controller
{      
    /** @var integer the display mode of the admin.
     * @see AdminParameters.*/
    public $adminDisplayMode;
    
    /**
    * Initializes the controller.
    * This method is called by the application before the controller starts to execute.
    */
    public function init()
    {        
        $this->setSide(\yiingine\web\Controller::ADMIN); //We are on the admin side.
        
        //Set the parameters for the client filter.
        $this->incompatibleClientsEntry = 'app.incompatible_admin_clients';
        
        parent::init();
        
        // Sets a special error handler for the admin section.
        Yii::$app->errorHandler->errorAction = '/admin/default/error';
        
        //ADMIN DISPLAY MODE
        
        // Set adminDisplayMode according to cookie presence.
        $adminDisplayMode = Yii::$app->request->cookies->getValue('adminDisplayMode');
        
        // Set adminDisplayMode according to the 'admin_display_mode' profile field if there is no cookie.
        if($adminDisplayMode === null && Yii::$app->user->getIdentity() && Yii::$app->user->getIdentity()->admin_display_mode !== '')
        {
            //Set a cookie to save the current display mode.    
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'adminDisplayMode',
                'expire' => 0,
                'value' => (int)(Yii::$app->user->getIdentity()->admin_display_mode)
            ]));
        }
        else if(!Yii::$app->user->isGuest) // If there is a cookie, use it or set the normal mode by default.
        {   
            $adminDisplayMode = (int)$adminDisplayMode;
            $this->adminDisplayMode = Yii::$app->params['adminDisplayMode'] = 
                ($adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE) || 
                        ($adminDisplayMode === null && in_array(Yii::$app->getIdentity()->username, Yii::$app->params['app.special_users'])) ?
                     AdminParameters::ADVANCED_DISPLAY_MODE : AdminParameters::NORMAL_DISPLAY_MODE;
        }
        
        /*If set, the module to which this controller belongs can use
         * its own layout.*/
        if(isset($this->module->adminLayout) && $this->module->adminLayout)
        {
            // Use the module's own layout.
            $this->layout = $this->module->adminLayout;
        }
        else
        {    
            // Sets the layout that will be used by this controller.
            $this->layout = '/admin';
        }
    }
    
    /**
    * Specifies the access control rules.
    * The result of this method is passed to the AccessControl filter.
    * @return array access control rules
    */
    public function accessRules()
    {
        /*An administrator or the user admin are permitted to do anything within,
        * the admin back-end. Other users are allowed to view admin section errors.*/        
       $rules = [
            [
                'allow' => true,
                'actions' => ['error'],
            ],
            [ 
                'allow' => true,
                'roles' => ['Administrator'],
            ],
            [ // These users are granted everything.
                'allow' => true, 
                'matchCallback' => function($rule, $action){ return !Yii::$app->user->isGuest && in_array(Yii::$app->user->getIdentity()->username, Yii::$app->params['app.special_users']); }
            ],
            [
                'allow' => false,  // Deny all other users.
            ],
        ];
        
        if($this->module) //If we are within a module.
        {
            //Add a role to manage every item in the module.
            array_unshift($rules, [
                'allow' => true,
                'roles' => [ucfirst($this->module->id).'Module-manage']
            ]);
        }
        
        return array_merge(parent::accessRules(), $rules);
    }
    
    /**
     * Returns the request parameters that will be used for action parameter binding.
     * Override of parent implementation to remove the returnUrl parameter.
     * @return array the request parameters to be used for action parameter binding
     */
    public function getActionParams()
    {
        $actionParams = parent::getActionParams();
        
        unset($actionParams['returnUrl']);
        
        return $actionParams;
    }
}
