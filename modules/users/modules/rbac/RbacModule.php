<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac;

use \Yii;

/**
 * Module class for the rbac module.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class RbacModule extends \yiingine\base\Module
{           
    /**
    * @var string Name of the role that grants permissions to users that are logged in.
    * This will be added as to CAuthManager::defaultRoles.
    */
    public $authenticatedRole = 'Authenticated';
    
    /**
    * @var string Name of the role that grants permissions to users that are not logged in.
    * This will be added to CAuthManager::defaultRoles.
    */
    public $guestRole = 'Guest';
    
    /** @return boolean if the current user can access this module.*/
    public function checkAccess()
    {
        return Yii::$app->user->can('Administrator') || parent::checkAccess();
    }
}
