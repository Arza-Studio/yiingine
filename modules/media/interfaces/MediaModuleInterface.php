<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\media\interfaces;

/**
 * An interface all modules working with media types must be implementing.
 */
interface MediaModuleInterface
{                    
    /**  
     * Modules using this class can have their own page for presentation within the website.
     * @param boolean $refresh retrieve the page from the database instead of the cache.
     * @return Medium a unique page for this module instance.
     * */
    public function getModuleModel();
}
