<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\behaviors;

use \Yii;

/**
 * This behavior logs changes to an active record.
 */
class ActiveRecordLogBehavior extends \yii\base\Behavior
{    
    /** @var array attributes to eclude. */
    public $exclude = ['ts_updt'];
    
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }
    
    /**
     * @inheritdoc.
     */
    public function attach($owner)
    {
        if(!\Yii::$app->getParameter('app.log_active_record_changes', true))
        {
            return; // Do not log changes.
        }
        
        parent::attach($owner);
    }
    
    /**
     * Log the upate/creation of the record.
     * @param Event $event event parameter.
     */
    public function afterSave($event)
    {    
        /* If we are in console mode and the table for the log entries does not yet exist
         * this means a migration is currently underway and the table for log entries has
         * not been created yet. */
        if(CONSOLE && Yii::$app->db->schema->getTable('active_record_changelog') === null)
        {
            return; // Skip logging changes.
        }
        
        if($event->name == yii\db\ActiveRecord::EVENT_AFTER_INSERT) // If this is an insertion.
        {
            $model = $this->getLogEntry();
            $model->scenario = 'ActiveRecordCreate';
            
            if(!$model->save())
            {
                dump($model->getErrors());
            }
        }
        else // This is an update.
        {            
            // Save the changes to every attribute that changed.
            foreach($event->changedAttributes as $attribute => $value)
            {                      
                if(in_array($attribute, $this->exclude)) // If changed on this attribute are not logged.
                {
                    continue;
                }
                
                if($value == $this->owner->$attribute) // If this is a false positive (happens with boolean attributes)
                {
                    continue;
                }
                /* URLs contained within attributes are dynamically replaced by {{baseURL}} when saved
                 * by some behaviors (like customFields's HtmlFieldBehavior). depending on when it occurs
                 * in the event chain, this modification could lead to false positive logs. Hence,
                 * the check for similarity is also done with the {{baseURL}} pattern in place. (see #1948)*/
                else if(!CONSOLE && str_replace(Yii::$app->request->hostInfo.Yii::$app->request->baseUrl, '{{baseURL}}', $this->owner->getAttribute($attribute)) == str_replace(Yii::$app->request->hostInfo.Yii::$app->request->baseUrl, '{{baseURL}}', $value))
                {
                    continue;    
                }
                
                $model = $this->getLogEntry();
                $model->scenario = 'ActiveRecordUpdate';
                
                $model->attribute = $attribute;
                $model->previous_attribute_value = $value;
                $model->new_attribute_value = $this->owner->$attribute;
                
                if(!$model->save()) // If saving failed.
                {
                    if(YII_DEBUG)
                    {
                        dump($model->getErrors());
                    }
                    else
                    {
                        throw new \yii\base\Exception('Could not log change on '.$model->model.'('.$model->model_id.')');
                    }
                }
            }   
        }
    }
    
    /**
     * Log the deletion of the record.
     * @param Event $event event parameter
     */
    public function afterDelete($event)
    {
        $model = $this->getLogEntry();
        $model->scenario = 'ActiveRecordDelete';
        $model->save();
    }
    
    /** @return ActiveRecordLogEntry a log entry ready to be filled.*/
    protected function getLogEntry()
    {        
        $model = new \yiingine\models\ActiveRecordLogEntry();
        $model->model = get_class($this->owner);
        $model->model_id = (string)$this->owner->getPrimaryKey();
        $model->model_table = $this->owner->tableName();
        
        // If the model implements AdministrableInterface, we can give more user-friendly information about it.
        if(in_array('yiingine\db\AdministrableInterface', class_implements($this->owner)))
        {
            if(!CONSOLE)
            {
                $model->model_admin_url = str_replace(Yii::$app->request->baseUrl, '', \yii\helpers\Url::to($this->owner->getAdminUrl()));
            }
            $model->model_title = (string)$this->owner;
        }
        
        if(CONSOLE) // There is no user in CONSOLE mode.
        {
            $model->user_name = 'Console';
            $model->user_id = 0;
        }
        else
        {
            $model->user_name = Yii::$app->user->isGuest ? 'Guest': Yii::$app->user->getIdentity()->username;
            $model->user_id = Yii::$app->user->isGuest ? 0: Yii::$app->user->id;
        }
        
        return $model;
    }
}
