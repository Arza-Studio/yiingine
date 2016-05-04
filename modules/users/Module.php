<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users;

use \yiingine\modules\users\models\User;
use \Yii;

/**
 * User module class.
 */
class Module extends \yiingine\modules\media\base\Module
{    
    /**
     * @var boolean
     * @desc use email for activation user account
     */
    public $sendActivationMail = true;
    
    /**
     * @var boolean activate user on registration (only $sendActivationMail = false)
     */
    public $activeAfterRegister = false;
    
    /**
     * @var boolean allow self-registration.
     */
    public $allowRegistration = false;
    
    /**
     * @var boolean allow password recovery.
     */
    public $allowPasswordRecovery = false;
    
    /**
     * @var boolean allow profile edition.
     */
    public $allowProfileEdition = false;
    
    /**
     * @var boolean allow users to deleted their own account.
     * */
    public $allowAccountDeletion = false;
    
    /**
     * @var boolean allow guests to view public profiles of registered users.
     * */
    public $allowPublicProfiles = false;
    
    /**@var array the url in a format that will be given to CHtml::normalizeUrl() which
     * the user will be redirected to when he logs out.*/
    public $returnLogoutUrl = ['/'];
    
    /**@var array the url in a format that will be given to CHtml::normalizeUrl() which
     * the user will be redirected to when he has registered and is logged in automatically.*/
    public $returnUrl = ['/users/profile/edit'];
    
    /**
     * @var boolean if the user should solve a captcha when registering.
     */
    public $doCaptchaAtRegistration = true;
    
    /**
     * @inheritdoc
     */
    public $moduleModelClass = 'yiingine\modules\users\models\Page';
    
    /** @return boolean if the current user can access this module.*/
    public function checkAccess()
    {
        return Yii::$app->user->can('User-view') ||
            $this->getModule('profileFields')->checkAccess() ||
            parent::checkAccess();
    }
    
    /**
     *  @inheritoc
     */
    public function getModuleModel($refresh = false)
    {        
        return $this->allowProfileEdition || $this->allowRegistration || $this->allowPublicProfiles ? parent::getModuleModel($refresh): null;
    }
}
