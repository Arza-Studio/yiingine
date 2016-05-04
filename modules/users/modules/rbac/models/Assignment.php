<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\models;

use \yiingine\modules\users\models\User;
use \Yii;

/**
 * This is the model class for an assignment between an item an a user.
 */
class Assignment extends AuthorizationObject
{        
    /** @var integer the user id to which this item is assigned.*/
    public $userId;
    
    /** @var Assignment the authorization item wrapped by this model.*/
    private $_assignment;
    
    /**
     * Returns a human readable name for the model. Actually not legal in PHP and in OO
     * (static belongs to a class) but an indication that this static method is available.
     * @param $plural boolean if the label should be plural
     * @return string the model's name
     */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{Assigment}other{Assignments}}', ['n' => $plural ? 2 : 1]);
    }
    
    /** The class constructor. 
     * @param Assignment $assignment an Assignment to be wrapped by this model.
     * @param string $scenario name of the scenario that this model is used in.*/
    public function __construct($assignment = null, $config = [])
    {
        parent::__construct($config);
        
        if($assignment) // If an assigment to wrap was provided.
        {            
            $this->name = $assignment->roleName;
            $this->userId = $assignment->userId;
            $this->createdAt = $assignment->createdAt;
            $this->isNewRecord = false;
            $this->_assignment = $assignment;
            
            $this->trigger(AuthorizationObject::EVENT_AFTER_FIND);
        }
    }
    
    /** 
     * @inheritdoc
     * */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs. */
        return array_merge(parent::rules(), [
            ['userId', 'required'],
            ['userId', 'exist', 'targetClass' => '\yiingine\modules\users\models\User', 'targetAttribute' => 'id'],
            ['name' ,'validateNameExists'],
            ['name' ,'validateUnique'],
            [['name', 'userId'], '\yiingine\validators\UnsafeValidator', 'on' => 'update'],
        ]);
    }
    
    /**
     * Validate the name attribute to make sure that it exists.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */    
    public function validateNameExists($attribute, $params)
    {
        // Check if the authorisation item exists.
        if(!$this->hasErrors($attribute) && !Yii::$app->authManager->getAssignment($this->$attribute, $this->userId))
        {
            $this->addError($attribute, Yii::t(__CLASS__, 'The authorization item does not exist.'));
        }
    }
    
    /**
     * Validate the name and id attributes to make sure that they are unique.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */    
    public function validateUnique($attribute, $params)
    {
        // Verify that the same assigment does not exist already.
        if(($this->getIsNewRecord() ||
             $this->name != $this->_assignment->roleName || $this->userId != $this->_assignment->userId) &&
            !$this->hasErrors('name') && 
            !$this->hasErrors('userId') && 
            Yii::$app->authManager->getAssignment($this->name, $this->userId)
        )
        {
            $this->addError($attribute, Yii::t(__CLASS__, 'This assigment already exists.'));
        }
    }
    
    /** 
     * @inheritdoc
     * */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'userId' => Yii::t(__CLASS__, 'User ID'),
        ]);
    }

    /** 
     * @inheritdoc
     * */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'name' => Yii::t(__CLASS__, 'The name of the authorization item to assign to the user.'),
            'userId' => Yii::t(__CLASS__, 'The user to which this item is assigned.'),
        ]);
    }

    /** 
     * @inheritdoc
     * */
    public function search()
    {
        $data = [];
        
        if($this->userId) // If a userId is being searched.
        {
            $userIds = [$this->userId];
        }
        else // Query all user ids.
        {
            $userIds = [];
            
            // Extract the ids from all users.
            foreach(User::find()->select(['id'])->all() as $user)
            {
                $userIds[] = $user->id;
            }
            
            /** TODO: Use a raw sql query to speed up the process. */
        }
        
        // Query the user ids we want.
        foreach($userIds as $userId)
        {
            // Convert Assigment objects to models while doing a search.
            foreach(Yii::$app->authManager->getAssignments($userId) as $assignment)
            {
                // If the items are being searched by name.
                if($this->name && mb_strpos($assigment->itemName, $this->name) === false)
                {
                    continue; // Skip this item.
                }
                
                $data[] = new self($assignment);
            }
        }
        
        return new \yii\data\ArrayDataProvider(['allModels' => $data, 'key' => 'name']);
    }
    
    /** 
     * @inheritdoc
     * */
    public function getId()
    {
        return $this->name && $this->userId ? $this->name.'-'.$this->userId: self::getModelLabel();
    }
    
    /** 
     * @inheritdoc
     * */
    protected function saveInternal()
    {
        if(!$this->validate()) // If the model did not validate.
        {
            return false; // Do not proceed with saving.
        }
        
        $authManager = Yii::$app->authManager;
        $role = $authManager->getRole($this->name);
        
        if(!$this->getIsNewRecord()) // If the assignment exists.
        {
            // Revoke it first.
            $authManager->revoke($role, $this->userId);
        }
        
        // Create an assigment.
        $this->_assignment = $authManager->assign($role, $this->userId);
        
        return true;
    }
    
    /** 
     * @inheritdoc
     * */
    protected function deleteInternal()
    {
        if($this->getIsNewRecord()) // If item is new.
        {
            throw new \yii\base\Exception('Cannot delete a new item.');
        }
        
        return Yii::$app->authManager->revoke(Yii::$app->authManger->getRole($this->_assignment->roleName), $this->_assignment->userId);
    }
}
