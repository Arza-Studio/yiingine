<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\web\admin;

/** 
 * An admin controller for authorization objects. 
 * */
abstract class AuthorizationObjectController extends \yiingine\web\admin\ModelController
{
    /** 
     * Initializes the controller.
     * */
    public function init()
    {
        // If authorization management has been disabled.
        if(!\Yii::$app->getParameter('enable_auth_management'))
        {
            throw new \yii\web\Forbidden\HttpException();
        }
        
        parent::init();
    }
    
    /**
    * Specifies the access control rules.
    * This method is used by the 'accessControl' filter.
    * @return array access control rules
    */
    public function accessRules()
    {
        /* Override ModelController's access rules. Only administrators should
         * be permitted to manipulate access control. */
        return \yiingine\gridController::accessRules();
    }
}
