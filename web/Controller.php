<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web;

use \Yii;

/**
 * This class describes a generic controller for the yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class Controller extends \yii\web\Controller
{    
    /**
     * @var integer id for the site side.
     * */
    const SITE = 0;
    
    /**
     * @var integer id for the admin side.
     * */
    const ADMIN = 1;
    
    /**
     * @var integer id for the API side.
     * */
    const API = 2;
    
    /**
     * @var int the side of the site we are on. Normally either SITE or ADMIN.
     */
    private $_side;
    
    /** @var string the name of the configuration entry that holds the incompatible_browsers
     * list.*/
    public $incompatibleClientsEntry = 'app.incompatible_browsers';
    
    /** @var array the route to the update client view. This attribute will
     * be passed to $this->redirect();*/
    public $updateClientRoute = ['/site/updateClient'];
    
    /** Sets the side of the application an also saves it as a parameter
     * for retrievel outside the scope of the controller.
     * @param integer side, the side the we application is on.*/
    protected function setSide($side)
    {
        $this->_side = $side;
        \Yii::$app->params['applicationSide'] = $side;
    }
    
    /**@return integer the side of application we are currently on.*/
    public function getSide() { return $this->_side; }
    
    /**
     * @return array controller behaviors.
     */
    public function behaviors()
    {
        return [
            'client' => new ClientFilter(),
            'access' => ['class' => '\yii\filters\AccessControl', 'rules' => $this->accessRules()]
        ];
    }
    
    /**
    * Specifies the access control rules.
    * The result of this method is passed to the AccessControl filter.
    * @return array access control rules
    */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'matchCallback' => function($rule, $action)
                {
                    // If no user is logged in.
                    if(!$user = Yii::$app->user->getIdentity())
                    {
                        return false;
                    }
                    
                    // These users can do everything.
                    if(in_array($user->username, Yii::$app->params['app.special_users']) ||
                        (Yii::$app->getParameter('enable_auth_management', false) &&
                         Yii::$app->authManager->getAssignment('Administrator', $user->id)
                     ))
                    {
                        return true;
                    }
                    
                    // If auth management is disabled.
                    if(!Yii::$app->getParameter('enable_auth_management', false))
                    {
                        return !Yii::$app->user->isGuest && $user->superuser; // Super users can access everything.
                    }
                    
                    return false;
                }
            ]
        ];
    }
    
    /** Runs the access control filter in place for this controller to verify if the current 
     * user has access to a certain action and method.
     * @param string $action the action to verify.
     * @param string $verb the HTTP verb to use.
     * @return boolean if the user has access to this action. */
    public function checkAccess($action, $verb = 'GET')
    {               
        foreach($this->accessRules() as $rule)
        {
            if(($result = ((new \yii\filters\AccessRule($rule))->allows(
                $this->createAction($action),
                Yii::$app->user,
                Yii::$app->request   
            ))) !== null) // If the rule applies to this case.
            {
                return $result; // The action is accessible.
            }
        }
        
        // The access rules do not allow the current user to do the requested action.
        return false;
    }
    
    /* Requires a php file using Yii's view path syntax.
     * @param string $file the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     */
    public function requireFile($file, $params = [])
    {
        return $this->getView()->requireFile($file, $params, $this);
    }
    
    /**
     * Clean every asset, cached file, etc. So they can be regenerated on the next request.
     * While more logical as an application level call (Yii::app()), it would not justify
     * the burden of loading a behavior just for this function.
     */
    protected function clean()
    {
        Yii::app()->cache->flush(); //Flushes the entire cache.
        Yii::app()->clientScript->flush(); //Flushed combined assets files.
        
        // Note : PHP cannot recurively delete directories and is generally very poor
        // at handling file io, so we just use a good old rm commande.
        
        // Remove all folders from the asset folder.
        exec('rm -rf '.Yii::app()->assetManager->basePath.'/*');
        
        // Flush all modified images.
        Yii::$app->imagine->flush();
    }
}

/** A filter for precenting outdated clients from usin the site and redirecting them to an
 * update client page.*/
class ClientFilter extends \yii\base\ActionFilter
{
    /**
     * Called before an action is run.
     * @param Action $action the action that is about to be run.
     * @retunr boolean false if the action should not be executed.
     * */
    public function beforeAction($action)
    {
            //Uncomment to test this functionnality with any client.
        /*if($filterChain->action->id != 'updateClient' &&
            !(isset(Yii::app()->request->cookies['ignoreUpdateClient']) &&
                Yii::app()->request->cookies['ignoreUpdateClient']->value == '1'))
        {
            $this->redirect($this->updateClientRoute);
        }*/
        
        /*If:
         * - There is a incompatible browsers config entry.
        * - The user's client is in that list.
        * - The action requested is not updateClient (otherwise we could cause a redirect loop).
        * - The ignore cookie is not set. */
        if( isset(Yii::$app->params[$this->owner->incompatibleClientsEntry][Yii::$app->params['platform']]) &&
            in_array(Yii::$app->params['browser'], Yii::$app->params[$this->owner->incompatibleClientsEntry][Yii::$app->params['platform']]) &&
            !(isset(Yii::$app->request->cookies['ignoreUpdateClient']) &&
                Yii::$app->request->cookies['ignoreUpdateClient']->value == '1') &&
            $action->id != 'updateClient')
        {
            $this->owner->redirect($this->owner->updateClientRoute); //Redirect to the updateClient page.
            return false;
        }
        
        return parent::beforeAction($action);
    }
}
