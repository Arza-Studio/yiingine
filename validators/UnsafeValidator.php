<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\validators;

/**
 * UnsafeValidator serves as a dummy validator whose main purpose is to mark the attributes to be unsafe for massive assignment.
 * It must be used in conjunction with \yiingine\db\ActiveRecord.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class UnsafeValidator extends \yii\validators\Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        /* safeAttributes() in \yiingine\db\ActiveRecord checks for the
         * existence of this validator and marks the attribute as unsafe when it is
         * found.*/
    }
}
