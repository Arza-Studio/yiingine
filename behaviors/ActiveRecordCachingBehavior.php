<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\behaviors;

/** Invalidates the cache group defined by the active record's class when it changes.
 */
class ActiveRecordCachingBehavior extends \yii\base\Behavior
{        
    /** @return array events (array keys) and the corresponding event handler methods (array values). */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }
    
    /**
     * Invalidates the cache group defined by the active record's class.
     * @param Event $event event parameter
     */
    public function afterSave($event)
    {
        $this->flushCache();
    }
    
    /**
     * Invalidates the cache group defined by the active record's class.
     * @param Event $event event parameter
     */
    public function afterDelete($event)
    {
        $this->flushCache();
    }
    
    /** Delete the cache group associated with this model. */
    public function flushCache()
    {
        /* Go up the hierarchy of classes to account for more generic
         * cache groups up to \yii\db\ActiveRecord.*/ 
        $class = get_class($this->owner);
        $shortClassName = substr($class, strrpos($class, '\\') + 1);
        
        do
        {
            // Delete groups defined by both the full (namespaced) and short class names.
            \yiingine\caching\GroupCacheDependency::deleteGroup(substr($class, strrpos($class, '\\') + 1));
            \yiingine\caching\GroupCacheDependency::deleteGroup($class);
            $class = get_parent_class($class);
        }
        while($class != 'yii\db\ActiveRecord');
        
        $pk = $this->owner->getPrimaryKey();
        
        if(is_array($pk)) // If this is a composite primary key.
        {
            $pk = implode('.', $pk); // Turn it to as string.
        }
        
        // Delete the group formed by this model in particular.
        \yiingine\caching\GroupCacheDependency::deleteGroup($shortClassName.'_'.$pk);
        \yiingine\caching\GroupCacheDependency::deleteGroup(get_class($this->owner).'_'.$pk);
    }
}
