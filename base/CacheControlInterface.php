<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\base;

/**
 * An interface for components that implement caching.
 */
interface CacheControlInterface
{
    const CACHE_NONE = 0;
    const CACHE_VIEW = 1;
    const CACHE_ALL = 2;
    
    /** @return integer the amount of time in seconds renders cached in this module should be valid.
     * @see COutpuCache*/
    public function getCacheDuration();
    
    /** @return integer the level of caching for this module. Either 0 for none, 1 for views only
     * and 2 for whole pages. */ 
    public function getCachingLevel(); 
}
