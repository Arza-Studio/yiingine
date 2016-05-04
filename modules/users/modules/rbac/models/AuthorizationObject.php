<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\models;

use \yiingine\interfaces;
use \Yii;
use \yii\base\ModelEvent;

/**
 * This is the model class for a generic authorization object, which normally is either
 * an item or an assigment.
 */
abstract class AuthorizationObject extends \yii\base\Model implements interfaces\DescriptiveInterface, interfaces\AdministrableInterface
{        
    /**
     * @event ModelEvent an event raised at the beginning of [[save()]]. You may set
     * [[ModelEvent::isValid]] to be false to stop the saving.
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';
    
    /**
     * @event Event an event raised at the end of [[save()]]
     */
    const EVENT_AFTER_SAVE = 'afterSave';
    
    /**
     * @event ModelEvent an event raised at the beginning of [[delete()]]. You may set
     * [[ModelEvent::isValid]] to be false to stop the deleting.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    
    /**
     * @event Event an event raised at the end of [[delete()]]
     */
    const EVENT_AFTER_DELETE = 'afterDelete';
    
    /**
     * @event Event an event raised after the object has been found.
     */
    const EVENT_AFTER_FIND = 'afterFind';
    
    /** @var string the name of the authorization item.*/
    public $name = '';
    
    /** @var integer UNIX timestamp of the object's creation. */
    public $createdAt = '';
    
    /** @var boolean if this authorization item is new.*/
    private $_isNewRecord = true;
    
    /** Called automatically by PHP after the model is cloned. This method takes care of 
     * reinitializing certain values of the cloned model.*/
    public function __clone()
    {
        // Make the model appear as if it was a new record.
        $this->isNewRecord = true;
        $this->scenario = 'insert';
    }
    
    /** @return array behaviors to attach to this model.*/
    public function behaviors()
    {
        return ['ActiveRecordLogBehavior' => '\yiingine\behaviors\ActiveRecordLogBehavior'];
    }
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs. */
        return [
            ['name', 'required'],
            ['name', 'match', 'pattern' => '/^[A-Za-z_0-9\-]+$/u'],
            ['name', 'string', 'max' => 127, 'min' => 4],
            ['name', 'safe', 'on' => 'search']
        ];
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t(__CLASS__, 'Name'),
        ];
    }

    /**
     * @return array customized attribute descriptions (name=>label)
     */
    public function attributeDescriptions()
    {
        return [
            'name' => Yii::t(__CLASS__, 'A unique name for the rule.'),
            'businessRule' => Yii::t(__CLASS__, 'A PHP boolean expression that defines if the authorization item applies.'),
            'data' => Yii::t(__CLASS__, 'A PHP array to be passed to the business rule.'),
        ];
    }
    
    /** @param string $attribute the name of the attribute from which a description is needed.
     * @return string the description.*/
    public function getAttributeDescription($attribute)
    {
        $descriptions  = $this->attributeDescriptions();
         
        return isset($descriptions[$attribute]) ? $descriptions[$attribute] : '';
    }
    
    /** 
     * @return string the equivalent of an id for this model.
     * */
    public abstract function getId();
    
    /** @return string the primary key for this model. In reality
     * it's the same as the id but its defined here for compatibility
     * purposes. */
    public function getPrimaryKey()
    {
        return $this->getId();
    }
    
    /** @param boolean $isNewRecord if this model is a new record.*/
    public function setIsNewRecord($isNewRecord)
    {
        $this->_isNewRecord = $isNewRecord;
    }
    
    /** @return boolean if this model is a new record.*/
    public function getIsNewRecord()
    {
        return $this->_isNewRecord;
    }
    
    /**
     * This method is invoked before saving an authorization object.
     * Make sure you call the parent implementation so that the event is raised properly.
     * @return boolean whether the saving should be executed. Defaults to true.
     */
    protected function beforeSave()
    {
        $event = new ModelEvent();
        
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);
        
        return $event->isValid;
    }
    
    /** Save the authorization item on the authorization manager.
     * @return boolean if the saving was sucessful.*/
    public final function save()
    {
        if($this->beforeSave()) // If saving can proceed.
        {
            if($result = $this->saveInternal())
            {
                $this->afterSave();
                $this->isNewRecord = false; // Record is no longer new.
            }
            
            return $result;
        }
        
        return false; // Saving failed.
    }
    
    /**
     * This method is invoked after saving an authorization object successfully.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    protected function afterSave()
    {
        $this->trigger(self::EVENT_AFTER_SAVE);
    }
    
    /** Save the authorization item on the authorization manager. This method
     * should be overriden by child classes to implement saving.
     * @return boolean if the saving was sucessful.*/
    protected abstract function saveInternal();
        
    /**
     * This method is invoked before deleting an authorization object.
     * Make sure you call the parent implementation so that the event is raised properly.
     * @return boolean whether the deleting should be executed. Defaults to true.
     */
    protected function beforeDelete()
    {
        $event = new ModelEvent();
        
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);
        
        return $event->isValid;
    }
    
    
    /** Deletes the authorization item.
     * @return boolean if deletion was sucessful.*/
    public final function delete()
    {
        if($this->beforeDelete()) // If deleting can proceed.
        {
            if($result = $this->deleteInternal())
            {
                 $this->afterDelete();   
            }
            
            return $result;
        }
        
        return false; // Deleting failed.
    }
    
    /**
     * This method is invoked after deleting an authorization object successfully.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    protected function afterDelete()
    {
        $this->trigger(self::EVENT_AFTER_DELETE);
    }
    
    /** 
     * Delete the authorization item from the authorization manager. This method
     * should be overriden by child classes to implement deleting.
     * @return boolean if the saving was sucessful.
     * */
    protected abstract function deleteInternal();
    
    
    /** @return string the url to the admin page for this model.*/
    public function getAdminUrl()
    {
        return \yii\helpers\Url::to(['/users/rbac/admin/'.mb_strtolower($this->formName()).'/update', 'id' => $this->getId()]);
    }
    
    /** @return boolean if the user has access to this model.*/
    public function isAccessible()
    {
        return Yii::$app->user->can('Administrator');
    }
    
    /** @return string a string representation of the model. */
    public function __toString()
    {
        return $this->formName().' ('.$this->getId().')';
    }
    
    /** 
     * @return boolean whether this model is enabled or not.
     * */
    public function getEnabled()
    {
        return true;
    }
}
