<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\models;

use \yiingine\modules\users\models\User;
use \Yii;

/**
 * LoginForm is the data structure for keeping user login form data. 
 */
class UserLogin extends \yii\base\Model
{    
    /** @var string the username the user is logging in with. */
    public $username;
    
    /** @var string the password the user is logging in with. */
    public $password;
    
    /** @var boolean if the user wants to be remembered. */
    public $rememberMe;
    
    /** @var User the user that is currently trying to log in.*/
    private $_user;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'authenticate'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'rememberMe' => Yii::t(__CLASS__, 'Remember me'),
            'username' => Yii::t(__CLASS__, 'Username or E-mail'),
            'password' => Yii::t(__CLASS__, 'Password'),
        ];
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     * @param string $attribute the attribute to authenticate.
     * @param string $params the parameters for the validator.
     */
    public function authenticate($attribute, $params)
    {
        // We only want to authenticate when there are no input errors.
        if($this->hasErrors()) 
        {
            return;            
        }
        
        if(mb_strpos($this->username,"@"))  //If username contains the @ character.
        {
            // User is identified by his email address.
            $this->_user = User::find()->where(['email' => $this->username])->one();
        }
        else 
        {
            // User is identified by his username.
            $this->_user = User::find()->where(['username' => $this->username])->one();
        }
        if($this->_user === null) // If user model not found.
        {
            if(mb_strpos($this->username,"@")) //If the user was identified by his email.
            {
                $this->addError('username', Yii::t(__CLASS__, 'Email is incorrect.'));
            } 
            else 
            {
                $this->addError('username', Yii::t(__CLASS__, 'Username is incorrect.'));
            }
        }
        // Else if the provided password did not match the one on record.
        else if(!Yii::$app->security->validatePassword($this->password, $this->_user->password))
        {
            $this->addError('password', Yii::t(__CLASS__, 'Password is incorrect.'));
        }
        else if($this->_user->status == User::STATUS_NOACTIVE) //If the account is not activated.
        {
            $this->addError('username', Yii::t(__CLASS__, 'You account is not activated.'));
        }
        else if($this->_user->status == User::STATUS_BANNED) //If the account has been banned.
        {
            $this->addError('username', Yii::t(__CLASS__, 'You account is blocked.'));
        }
        // If a non superuser is trying to log into the admin.
        else if($this->scenario == 'adminLogin' && !$this->_user->superuser)
        {
            // Show an error.
            $this->addError('username', Yii::t(__CLASS__, 'Username is incorrect.'));
        }
        else
        {
            // Credentials were correctly provided.
            $this->_user->scenario = 'login';
        }
    }
    
    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully.
     */
    public function login()
    {
        if(!$this->validate()) 
        {
             return false;
        }
        
        // If login failed.
        if(!$result = \Yii::$app->user->login($this->_user, $this->rememberMe ? 3600 * 24 * 30 : 0))
        {
            throw new \yii\web\HttpException(503, 'There was a problem during login.'); // Service unavailable.
        }
        
        // If user login has been temporarily disabled, users with enough rights can always log in.
        if( !in_array($this->_user->username, Yii::$app->params['app.special_users']) &&
            !Yii::$app->user->can('YiingineBlockBypass') &&
            Yii::$app->getParameter('yiingine.users.disable_user_accounts', false)
        )
        { 
            Yii::$app->user->logout();
            // Service unavailable.
            throw new \yii\web\HttpException(503, Yii::t(__CLASS__, 'User accounts have been disabled'));
        }
        
        //Set the lastvisit date time.
        $this->_user->detachBehavior('ActiveRecordLockingBehavior');
        $this->_user->lastvisit = date(\yiingine\libs\Functions::$MySQLDateTimeFormat, time());
        
        if(!$this->_user->save()) // If saving the user failed. 
        { 
            throw new \yii\base\Exception($this->_user->getFirstError());
        }
        
        return true;
    }
}
