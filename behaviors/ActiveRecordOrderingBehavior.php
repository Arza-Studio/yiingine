<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\behaviors;

/** This behavior defines mechanisms that allow the grouping of models together,
 * such as in a menu tree.
 */
class ActiveRecordOrderingBehavior extends \yii\base\Behavior
{
    /** @var array the previous values of the owner's attributes. */
    public $groupingAttributes = [];
    
    /** @var string the attribute that stores the position within the grouping.*/
    public $attribute = 'position';
    
    /** @return array events (array keys) and the corresponding event handler methods (array values). */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave'
        ];
    }
    
    /** @return array the values of all the grouping attributes.*/
    public function getGroupingValues()
    {
        $array = array();
        
        foreach($this->groupingAttributes as $attribute)
        {
            $array[$attribute] = $this->owner->$attribute;
        }
        
        return $array;
    }
    
    /**
     * Re-order the active records if the position of the owner has changed.
     * @param Event $event event parameter
     */
    public function afterSave($event)
    {        
        // Check if the position we are moving is not 0.
        if((int)$this->owner->{$this->attribute} === 0)
        {
            return;
        }
        
        $query = $this->owner->find()
            ->where([$this->attribute => $this->owner->{$this->attribute}])
            ->andWhere(['<>', 'id', $this->owner->id])
            ->andWhere($this->owner->getAttributes($this->groupingAttributes));
        
        // Check if a model is currently occupying the position we are moving to.
        if(!$next = $query->one()) // Exclude the owner.
        {
            return; // No model is currently there so nothing to do.
        }
        // The position is occupied.
        
        // If the model is not a new record and its position was moved by one.
        if(!$this->owner->isNewRecord && abs((int)$event->changedAttributes[$this->attribute] - (int)$this->owner->{$this->attribute}) === 1)
        {
            // Swap the two models.
            $next->{$this->attribute} += (int)$event->changedAttributes[$this->attribute] - (int)$this->owner->{$this->attribute};
            $next->detachBehavior('ActiveRecordOrderingBehavior'); // Otherwise the next model would move back in place.
            $next->detachBehavior('ActiveRecordLockingBehavior'); // Locking data was probably not part of the request.
            $next->save();
            
            return;
        }
        // else if($this->owner->isNewRecord)
        // {
            // The model will be moved by the code below.
        //}
        // Else the model was moved by several positions.
        
        // Check if there is a model at the first position.
        
        $query = $this->owner->find()
            ->where([$this->attribute => 1])
            ->andWhere(['<>', 'id', $this->owner->id]) // Exclude the owner.
            ->andWhere($this->owner->getAttributes($this->groupingAttributes));
            
        if(!$query->count())
        {
            // Move models that have a position lower than the owner's position down.
            $direction = '<=';
            $count = -1;
        }
        else
        {
            // Move models that have a position greater than the owner's position up.
            $direction = '>=';
            $count = 1;
        }
        
        $query = $this->owner->find()
            ->where([$direction, $this->attribute, $this->owner->{$this->attribute}])
            ->andWhere(['<>', 'id', $this->owner->id]) // Exclude the owner.
            ->andWhere($this->owner->getAttributes($this->groupingAttributes));
        
        $this->owner->updateAllCounters([$this->attribute => $count], $query->where);
    }
}
