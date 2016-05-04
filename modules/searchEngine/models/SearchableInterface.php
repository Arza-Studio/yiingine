<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\searchEngine\models;

/**
 * An interface for models that can be searched. Along with the functions here, the model
 * must define a scope named "clientSearch".
 */
interface SearchableInterface
{
    /** 
     * @return mixed a list of the attributes that can be searched or
     * false if the model cannot be searched. 
     * */
    public static function getSearchableAttributes();
}
