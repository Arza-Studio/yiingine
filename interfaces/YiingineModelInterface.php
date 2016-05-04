<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\interfaces;

/**
 * An interface that all models used by the yiingine should define.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
interface YiingineModelInterface
{
    /**
     * Returns a human readable name for the model.
     * @param $plural boolean if the label should be plural
     * @return string the model's name
     */
    public static function getModelLabel($plural = false);    
}
