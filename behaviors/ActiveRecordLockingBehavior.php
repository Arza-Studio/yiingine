<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\behaviors;

/** Implements optimistic locking for an ActiveRecord. Optimistic locking verifies
 * that the last update time in the submitted form and that of the model match.
 * 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class ActiveRecordLockingBehavior extends \yii\base\Behavior
{
    /** @var array the update times of all the models loaded with this behavior.*/
    public static $lastUpdateTimes = array();
    
    /** @var string the last modified time as written in the database.*/
    private $_dbLastUpdateTime;
    
    /** @var boolean of the owner has been saved already.*/
    private $_saved = false;
    
    /** @var string The last update time of this record at the time the record was retrieved.
     * If the record is used in a form, this value will be a hidden field in the form that will
     * be compared with the update time on database to check for edit conflicts.*/
    public $lastUpdateTime;
    
    /** @return array events (array keys) and the corresponding event handler methods (array values). */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            \yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate'
        ];
    }
    
    /**
     * Attaches the behavior object to the component. Override of parent to prevent
     * attachment of this behavior in console mode.
     * @param Component $owner the component that this behavior is to be attached to.
     */
    public function attach($owner)
    {
        if(CONSOLE)
        {
            return; // No optimistic locking in CONSOLE mode.
        }
        
        parent::attach($owner);
        
        if(!$owner->isNewRecord)
        {
            /* Run the afterFind method in case this behavior was added 
             * after the model was retrieved from the database.*/
            $this->afterFind(new CEvent());
        }
    }
    
    /**
     * @inheritdoc
     */
    public function detach()
    {
        unset(self::$lastUpdateTimes[$this->getId()]);
        unset($_POST[$this->getId()]);
        
        parent::detach();
    }
    
    /**
     * Validates that no edit conflict has happened.
     * @param Event $event event parameter
     */
    public function beforeValidate($event)
    {
        // No optimistic locking on new records.
        if(!$this->owner->isNewRecord && Yii::app()->request->isPostRequest && !$this->_saved)
        {            
            /* If the model was not retrieved within the request (it could have
             * been retrieved from cache. Do not use optimistic locking. */
            if(!isset(self::$lastUpdateTimes[$this->getId()])) 
            {
                return;
            }
            
            if(!isset($_POST[$this->getId()]))
            {
                return; // The model's last update time was not part of the request. Do not lock it.
                //throw new CHttpException(400, $this->getId().' missing.'); // Bad Request.    
            }

            $this->lastUpdateTime = $_POST[$this->getId()];
            
            //If the last update time is earlier than the one in the db.
            if(strcasecmp($this->lastUpdateTime, $this->_dbLastUpdateTime) < 0)
            {
                //dump($this->owner->ts_updt.' -- '.$this->lastUpdateTime. ' -- '. $this->_dbLastUpdateTime);
                // This means there is an edit conflict.
                $this->owner->addError('lastUpdateTime', Yii::t(__CLASS__, 'There is an edit conflict on this record. Please reload it.'));
                $event->isValid = false; // Stop the validation process.
            }
        }
    }
    
    /**
     * Updates the last update time.
     * @param CEvent $event event parameter
     */
    public function afterSave($event)
    {
        /* Detach this behavior in case the model gets saved again.
         * During multiple saves, the ts_updt value will change but not that
         * of the lastUpdateField value in the form which will cause erroneous
         * warnings of edit conflicts. */
        // DOES NOT WORK DUE TO A BUG WITH YII!
        //$this->owner->detachBehavior('ActiveRecordLockingBehavior');
        $this->_saved = true;
        self::$lastUpdateTimes[$this->getId()] = $this->lastUpdateTime = $this->_dbLastUpdateTime = $this->owner->ts_updt;
    }
    
    /**
     * Save the model's attributes
     * @param Event $event event parameter
     */
    public function afterFind($event)
    {
        self::$lastUpdateTimes[$this->getId()] = $this->lastUpdateTime = $this->_dbLastUpdateTime = $this->owner->ts_updt;
    }
    
    /** @return string the id of the field used to save the last update time.*/
    public function getId()
    {
        return 'lastUpdateTime_'.get_class($this->owner).$this->owner->id;    
    }
}
