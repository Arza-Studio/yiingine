<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\users\models;

use \Yii;
use \yiingine\modules\users\parameters\Requirement;
use \yiingine\modules\users\parameters\Visible;
use \yiingine\modules\users\UsersModule;
use \yiingine\libs\Functions;

/** The user model represents the most basic facilities for user management. Individual deployments
 * of the user module can supplement this user's data schema with custom fields.
 * */
class User extends \yiingine\modules\customFields\models\CustomizableModel implements 
    \yiingine\db\AdministrableInterface, 
    \yiingine\db\ViewableInterface, 
    \yiingine\modules\searchEngine\models\SearchableInterface,
    \yii\web\IdentityInterface
{
    //User status.
    const STATUS_NOACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = -1;
    
    /**@var array an attribute cache for user profile fields.*/
    private static $_fields = array();
    
    /** @var string the password for verification.*/
    public $verifyPassword;
    
    /** @var array the roles associated to this user.*/
    public $roles;
    
    /**
    * @see \yiingine\db\ModelInterface::getModelLabel()
    */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{User}other{Users}}', ['n' => $plural ? 2 : 1]);
    }
    
    /** @return string a string representation of the model. */
    public function __toString() { return $this->username; }
    
    /**
     * @return string the associated database table name
     */
    public static function tableName(){ return 'users'; }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        
        $rules = [
            [['password', 'verifyPassword'], 'required'],
            ['password', 'string', 'max' => 128, 'min' => 4,'message' => Yii::t(__CLASS__, 'Incorrect password (minimal length 4 symbols).')],
            ['verifyPassword', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t(__CLASS__, 'Retype Password is incorrect.')],
            ['activation_key', '\yiingine\validators\UnsafeValidator']
        ];
                
        switch($this->scenario)
        {
            case 'recovery':
                return $rules;
            case 'activation':
                // Only the status can be modified.
                return [
                    ['status', 'in', 'range' => [self::STATUS_NOACTIVE, self::STATUS_ACTIVE, self::STATUS_BANNED]],
                    ['status', 'integer', 'integerOnly' => true],
                ];
            case 'login':
                // Only last visit can be set.
                return [['lastvisit', 'date' ,'format' => Functions::$MySQLDateTimeYiiFormat, 'on' => 'login']];
            case 'userView':
                foreach($this->getManagers() as $manager)
                {
                    $field = $manager->getField();
                    
                    if($field->visible == Visible::VISIBLE_NO ||
                       $field->visible == Visible::VISIBLE_ADMIN_INTERFACE ||
                       (Yii::$app->user->isGuest && $field->visible == Visible::VISIBLE_REGISTER_USER) ||
                        (!Yii::$app->user->isGuest && $field->visible == Visible::VISIBLE_ONLY_OWNER)
                    )
                    {
                        $rules[] = [$manager->getAttribute(), '\yiingine\validators\UnsafeValidator'];
                    }
                }
            case 'default':
            case 'search':
                $rules = array_merge($rules, parent::rules(), [
                    ['status', 'in', 'range' => [self::STATUS_NOACTIVE, self::STATUS_ACTIVE, self::STATUS_BANNED]],
                    [['status', 'superuser'], 'required'],
                    ['lastvisit', 'date' ,'format' => Functions::$MySQLDateTimeYiiFormat],
                    ['status', 'integer', 'integerOnly' => true],
                    ['superuser', 'boolean'],
                    [['username', 'email', 'lastvisit', 'status', 'superuser'], 'safe', 'on'=>'search'],
                ]);
                
                // Only administrators can assign roles.
                if(!CONSOLE && Yii::$app->authManager && Yii::$app->authManager->checkAccess(Yii::$app->user->id, 'Administrator'))
                {
                    $rules[] = ['roles', 'validateRoles'];
                }
                else
                {
                    // No validator defined for roles so make it an unsafe attribute.
                    $rules[] = ['roles', '\yiingine\validators\UnsafeValidator'];
                }
                
            case 'userEdit':
            case 'registration':
                /* Generate rules for profile fields.
                 * Do not add rules from custom fields the normal way because rules
                 * for fields only present in admin forms should have validators to 
                 * prevent massively assigning them.*/
                $scenario = $this->scenario;
                $this->scenario = 'default'; // Get all fields to mark the ones not visible as unsafe.
                foreach($this->getManagers() as $manager)
                {
                    $field = $manager->getField();
                    
                    // If the field is not supposed to be visible.
                    if($field->visible == Visible::VISIBLE_NO || $field->visible == Visible::VISIBLE_ADMIN_INTERFACE )
                    {
                        // Skip to make the field unsafe to assign.
                        $rules[] = [$manager->getAttribute(), '\yiingine\validators\UnsafeValidator'];
                        continue;
                    }
                    
                    // Check the requirements for this field.
                    // NOTE! This will be added above the normal "required" attribute.
                    switch($field->requirement)
                    {
                        case Requirement::REQUIRED_NO:
                        case Requirement::REQUIRED_NO_SHOW_REG:
                            break; //Nothing to do here.
                        case Requirement::REQUIRED_YES_NOT_SHOW_REG:
                            if($this->scenario == 'registration') //If the user is not registering.
                            {
                                break; //This field is not required.
                            }
                        case Requirement::REQUIRED_YES_SHOW_REG:
                            $rules[] = [$manager->getAttribute(), 'required']; //This field is required.
                            break;
                        default:
                            throw new \yii\web\ServerErrorHttpException('illegal requirement');
                    }
                }
                $this->scenario = $scenario;
                
                $rules = array_merge(parent::rules(), [            
                    ['email', 'required'],
                    ['username', 'required'],
                    ['username', 'unique', 'message' => Yii::t(__CLASS__, 'This user name already exists.')],
                    ['username', 'string', 'max' => 20, 'min' => 3,'message' => Yii::t(__CLASS__, 'Incorrect username (length between 3 and 20 characters).')],
                    ['username', 'in', 'not' => true, 'range' => ['engine', 'console']],
                    ['username', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/u','message' => Yii::t(__CLASS__, 'Only letters, capitals and numbers are permitted, no spaces and special characters (A-z0-9).')],
                    ['email', 'unique', 'message' => Yii::t(__CLASS__, 'This user email address already exists.')],
                    ['email', 'email'],
                    // No validator defined for these attributes to make them unsafe.
                    //array('superuser, roles, status, lastVisit', '\yiingine\validators\UnsafeValidator', 'on' => 'registration, userEdit'),
                ], $rules);
                
                /* Add this rule only for users whose names are not part of the special names list otherwise
                 * users with special names cannot modify their account from the profile page.*/
                $specialNames = array_merge(['demo', 'webmaster'], Yii::$app->params['app.special_users']);
                if(!CONSOLE && (Yii::$app->user->isGuest || !in_array(Yii::$app->user->getIdentity()->username, $specialNames)))
                {
                    $rules[] = ['username', 'in', 'not' => true, 'range' => $specialNames, 'on'=> 'registration, userEdit'];
                }
                
                break;
            default:
                throw new \yii\base\Exception('unknown scenario: '.$this->scenario);    
        }
        
        return $rules;
    }
    
    /**
     * Validate the roles field so selected roles are part of the roles list.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateRoles($attribute, $params)
    {
        //If RBAC is disabled.
        if(!isset(Yii::app()->params['enable_auth_management']) || !Yii::app()->params['enable_auth_management'])
        {
            return; //No validation is done.
        }
        
        if(!isset($this->$attribute) || !$this->$attribute) //If the attribute is not set.
        {
            return; //Do not validate it.
        }
        
        $roles = array(); // Available roles.
    
        // Make a list of all available roles.
        foreach(Yii::app()->authManager->roles as $role)
        {
            $roles[] = $role->name;
        }
        
        // Check if the selected roles exist.
        foreach($this->roles as $role)
        {
            if(!in_array($role, $roles))
            {
                throw new CException('Role does not exist!');
            }
        }
    }
    
    /** Caches the custom fields.
     * @return array the CustomFields associated with this model. */
    public function getFieldModels()
    {        
        // If not scenario is set, use the default one.
        if(!isset($this->scenario)) { $this->scenario = 'default'; }
        
        \yiingine\modules\customFields\models\CustomField::$module = Yii::$app->getModule('users')->getModule('profileFields');
        
        if(!isset(self::$_fields[$this->scenario])) //If we do not have the fields in cache.
        {
            if(($fields = Yii::$app->cache->get('UserProfileFields_'.$this->scenario)) === false)
            {   
                $query = \yiingine\modules\customFields\models\CustomField::find()->orderBy('position ASC')->with('formGroup');
                
                switch($this->scenario)
                {
                    case 'activation':
                    case 'login':
                    case 'recovery':
                        return []; // Fields are not needed for this scenario.
                    case 'userView':
                    case 'userEdit': // User is editing his profile.
                        $query->where(['in_forms' => 1]); // Display fields that are in forms.
                        $query->andWhere(['!=', 'visible', \yiingine\modules\users\parameters\Visible::VISIBLE_NO]);
                        $query->andWhere(['!=', 'visible', \yiingine\modules\users\parameters\Visible::VISIBLE_ADMIN_INTERFACE]);
                        break;
                    case 'registration': // User is registering.
                        $query->where(['!=', 'requirement', \yiingine\modules\users\parameters\Requirement::REQUIRED_YES_NOT_SHOW_REG]);
                        $query->andWhere(['!=', 'requirement', \yiingine\modules\users\parameters\Requirement::REQUIRED_NO]);
                        $query->andWhere(['!=', 'visible', \yiingine\modules\users\parameters\Visible::VISIBLE_NO]);
                        $query->andWhere(['!=', 'visible', \yiingine\modules\users\parameters\Visible::VISIBLE_ADMIN_INTERFACE]);
                        break;
                    case 'default':
                    case 'search':
                    case '':    
                        break;    
                    default:
                        throw new \yii\web\ServerErrorHttpException('Illegal scenario: '.$this->scenario);
                }
                
                $fields = [];
                // Retrieve the models from database.
                foreach($query->all() as $field)
                {
                    $fields[$field->name] = $field;
                }
                
                // Cache the retrieved fields.
                Yii::$app->cache->set('UserProfileFields_'.$this->scenario, $fields, 0, new \yiingine\caching\GroupCacheDependency(['CustomField', 'FormGroup']));
            }
            self::$_fields[$this->scenario] = $fields; //Save the fields in cache.
        }
        
        return self::$_fields[$this->scenario]; //Return the profile fields for this scenario.
    }
    
    /** @return CustomFieldsModule the module for this model's custom fields.*/
    public function getCustomFieldsModule()
    {
        return Yii::$app->getModule('users')->getModule('profileFields');
    }
    
    /**
     * Override parent implementation to set the last visit time and encrypt the password
     * if the record is new.
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $module = Yii::$app->getModule('users');

        if($this->isNewRecord)
        {
            $this->lastvisit = date(Functions::$MySQLDateTimeFormat, time());
        }
        
        if($this->isAttributeChanged('password')|| $this->isNewRecord)
        {
            $this->password = Yii::$app->security->generatePasswordHash($this->password);
            $this->activation_key = Yii::$app->security->generateRandomString();
        }
        
        switch($this->scenario) // Some scenario trigger some changes to the data.
        {
            /* A user knowing the recovery key of another user could reuse it to change his password. */
            case 'recovery':
            /* The activation key needs to be reencrypted,
            otherwise, a user could change his status by reactivating his account.*/
            case 'activation':
                // For both these scenarios the activation_key changes because it was used to manipulate the account.
                $this->activation_key = Yii::$app->security->generateRandomString();
                break;
        }
        
        return parent::beforeSave($insert);
    }
    
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->verifyPassword = $this->password;
        
        return parent::afterFind();
    }
    
    /**
     * Override of parent implementation to save roles.
     * @inheritdoc
     * */
    public function afterSave($insert, $changedAttributes)
    {    
        parent::afterSave($insert, $changedAttributes);
        
        //If RBAC is enabled and roles have been set.
        if(!CONSOLE && isset($this->roles) && Yii::app()->getParameter('enable_auth_management'))
        {
            $authManager = Yii::app()->authManager;
            
            //First remove all authorization items associated with this user.
            foreach($authManager->getRoles($this->id) as $role)
            {
                // If an administrator is modifying their own account.
                if(Yii::app()->user->id == $this->id && 
                    Yii::app()->checkAccess('Administrator') &&
                    $role->name == 'Administrator'
                )
                {
                    continue; // An administrator cannot revoke his Administrator role.
                }
                
                $authManager->revoke($role->name, $this->id);
            }
            
            if($this->roles)
            {
                //Then recreate all authorization items.
                foreach($this->roles as $role)    
                {
                    $authManager->assign($role, $this->id);
                }
            }
            $authManager->save(); //Save to persistent storage.    
        }    
    }
    
    /**
     * @inheritdoc
     * */
    public function afterDelete()
    {
        // Override of parent implementation to delete roles. 
        
        parent::afterDelete(); //Calls the parent.
        
        // If RBAC is enabled.
        if(Yii::$app->getParameter('enable_auth_management', true))
        {
            // Revoke all authorization items associated to this user
            Yii::$app->user->authManager->revokeAll($this->id);
        }
        
        /* Delete all traces of the user. The one thing that cannot be removed
         are entries in the active record logs. */
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge([
            'username' => Yii::t(__CLASS__, 'Username'),
            'password' => Yii::t(__CLASS__, 'Password'),
            'verifyPassword' => Yii::t(__CLASS__, 'Retype Password'),
            'email' => Yii::t(__CLASS__, 'E-mail'),
            'verifyCode' => Yii::t(__CLASS__, 'Verification Code'),
            'activation_key' => Yii::t(__CLASS__, 'Activation key'),
            'lastvisit' => Yii::t(__CLASS__, 'Last visit'),
            'status' => Yii::t(__CLASS__, 'Status'),
            'superuser' => Yii::t(__CLASS__, 'Superuser'),
            'roles' => Yii::t(__CLASS__, 'Roles'),
        ], parent::attributeLabels());
    }
    
    /**@return array a user friendly description of this model's attributes.*/
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'activation_key' => Yii::t(__CLASS__, 'The activation key is used for account activation and password recovery.'),
            'roles' => Yii::t(__CLASS__, 'The roles associated to this user. A role is a set of permissions and is created using the access control module.'),
            'status' => Yii::t(__CLASS__, 'The status of this account.'),
            'superuser' => Yii::t(__CLASS__, 'If the user has superuser rights (ie: inconditional access to the admin interface).'),
            'username' => Yii::t(__CLASS__, 'The user name. Between 3 and 20 characters long.'),
        ]);
    }
    
    /**
     * Translates a status code to as string.
     * @param integer $status the status code.
     * */
    public static function getStatusLabel($status) 
    {
        switch($status)
        {
            case self::STATUS_NOACTIVE: return Yii::t(__CLASS__, 'Not active');
            case self::STATUS_ACTIVE: return Yii::t(__CLASS__, 'Active');
            case self::STATUS_BANNED: return Yii::t(__CLASS__, 'Banned');
            default: return 'Unknown status';
        }
    }
    
    /** 
     * @inheritdoc
     * */
    public function getAdminUrl()
    {
        return $this->isNewRecord || !$this->id ?
            ['/users/admin/user/create'] : 
            ['/users/admin/user/update', 'id' => $this->id];

    }
    
    /** 
     * @inheritdoc
     * */
    public function isAccessible()
    {
        return Yii::app()->user->checkAccess(get_class($this).'-view'); //Use normal access checking.    
    }
    
    /** 
     * @inheritdoc
     * */
    public function getEnabled() { return $this->status == self::STATUS_ACTIVE; }
    
    /** 
     * @inheritdoc
     * */
    public function getUrl()
    {
        // If public profiles are disabled.
        if(!Yii::$app->getModule('users')->allowPublicProfiles)
        {
            return false;
        }
        
        if($this->status == self::STATUS_NOACTIVE) // If the user is not active.
        {
            return false;
        }
        
        return ['/users/profile/index', 'id' => $this->id];
    }
    
    /** 
     * @inheritdoc
     * */
    public function getTitle($html = false)
    {
        return $this->username.' ('.$this->id.')';
    }
    
    /** 
     * @inheritdoc
     * */
    public function getDescriptor()
    {
        // If model() is used, the record will not be new but the id will be null.
        return $this->isNewRecord || !$this->id ? self::getModelLabel(): $this->getTitle();
    }
    
    /** 
     * @inheritdoc
     * */
    public function getThumbnail()
    {
        return false;
    }
    
    /** 
     * @inheritdoc
     * */
    public function getContent()
    {
        return false;
    }
    
    /** 
     * @inheritdoc
     * */
    public function getDescription()
    {
        return $this->getTitle();
    }
    
    /** 
     * @inheritdoc
     * */
    public static function getSearchableAttributes()
    {
        return ['username', 'id', 'email'];
    }
    
    /** 
     * @inheritdoc
     * */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /** 
     * @inheritdoc
     * */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /** 
     * @inheritdoc
     * */
    public function getId()
    {
        return $this->id;
    }

    /** 
     * @inheritdoc
     * */
    public function getAuthKey()
    {
        return $this->activation_key;
    }

    /** 
     * @inheritdoc
     * */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
