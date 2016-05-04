<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\behaviors;

use \Yii;

/** Sets the dt_crtd and ts_updt fields when an active record changes.
 */
class ActiveRecordTimeStampingBehavior extends \yii\base\Behavior
{        
    /** @return array events (array keys) and the corresponding event handler methods (array values). */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave'
        ];
    }
    
    /**
     * Sets the dt_crtd and ts_updt timestamps.
     * @param Event $event event parameter
     */
    public function beforeSave($event)
    {
        if($this->owner->isNewRecord) //If this is a new record.
        {
            //Sets the datetime created attribute.
            $this->owner->dt_crtd = date(\yiingine\libs\Functions::$MySQLDateTimeFormat);
        }
        
        /* Timestamps could be automatically get updated using the MySQl on update event but
         * this would further break compatibility with other SQL engines. Plus, Yii
         * always saves the ts_updt field it fetches when loading the model which probably
         * overwrites the value set by the event.*/
        $this->owner->ts_updt = date(\yiingine\libs\Functions::$MySQLDateTimeFormat);
    }
    
    /**
     * @return array the labels for the attributes required by this behavior.
     */
    public function attributeLabels()
    {
        return [            
            'dt_crtd' => Yii::t(__CLASS__, 'Date/Time Created'),
            'ts_updt' => Yii::t(__CLASS__, 'Timestamp Updated'),
        ];
    }
    
    /** @return array a user friendly description for the attributes required by this behavior.*/
    public function attributeDescriptions()
    {
        return [            
            'ts_updt' => Yii::t(__CLASS__, 'The date/time at which this item was last updated.'),
            'dt_crtd' => Yii::t(__CLASS__, 'The date/time at which this item was created.')
        ];
    }
}
