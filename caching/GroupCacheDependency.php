<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\caching;

/** A cache dependency for grouping cache entries. If a group is invalidated, all
 * entries that are part of this group will get invalidated as well.*/
class GroupCacheDependency extends \yii\caching\Dependency
{        
    /**@var array the groups this cache entry is part of.*/
    private $_groups = [];
    
    /** The class constructor.
     * @param array $groups the groups this cache entry is part of.*/
    public function __construct($groups)
    {
        $this->_groups = $groups;
        $this->reusable = false; // This cache dependency cannot be reused.
    }
    
    /** Invalidates a cache group. All entries tied to this group will be refreshed.
     * @param string $group the name of the group to invalidate.*/
    public static function deleteGroup($group)
    {
        \Yii::$app->cache->set($group.'_group', uniqid());
    }
    
    /**
     * Generates the data needed to determine if dependency has been changed by
     * comparing the unique id of each group it has in memory with their current
     * unique id.
     * @param yii\caching\Cache $cache The cache component that is currently evaluating this dependency.
     * @return mixed the data needed to determine if dependency has been changed.
     */
    protected function generateDependencyData($cache)
    {
        $data = [];
        foreach($this->_groups as $group)
        {
            $data[$group] = $cache->get($group.'_group');
        }
        return $data;
    }
}
