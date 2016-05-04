<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\models;

use \Yii;

/**
 * UserRecoveryForm is the data structure for keeping user recovery form data. 
 */
class UserRecoveryForm extends \yii\base\Model 
{
    /** 
     * @var string the login or email for recovering the account.
     * */
    public $loginOrEmail;
    
    /**
     * @var integer the recovered user id.
     * */
    public $userId;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['loginOrEmail', 'required'],
            ['loginOrEmail', 'match', 'pattern' => '/^[A-Za-z0-9@.\-\s]+$/u', 'message' => Yii::t(__CLASS__, 'Incorrect symbols (A-z0-9).')],
            ['loginOrEmail', 'checkExists'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ['loginOrEmail' => Yii::t(__CLASS__, 'Username or E-mail')];
    }
    
    /** 
     * Custom validator for checking if the provided username or email matches a user
     * in the database.
     * @param string $attribute the attribute being validated.
     * @param string $params the provided parameter.
     * */
    public function checkExists($attribute, $params) 
    {
        if(!$this->hasErrors())  // Only authenticate when there are no input errors
        {
            $model = User::find()->where(mb_strpos($this->loginOrEmail, '@') ? 
                    ['email' => $this->loginOrEmail] :
                    ['username' => $this->loginOrEmail]
            )->one();

            if($model === null) // User was not fount.
            {
                $this->addError('loginOrEmail', mb_strpos($this->loginOrEmail, '@') ? Yii::t(__CLASS__, 'Email is incorrect.') : Yii::t(__CLASS__, 'Username is incorrect.'));
            }
            else //User was found.
            {
                $this->userId = $model->id;
            }    
        }
    }
    
}
