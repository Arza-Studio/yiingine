<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media;

use \Yii;

/**
 * Media module class.
 */
class Module extends base\Module
{            
    /** 
     * @inheritdoc
     * */
    public function checkAccess()
    {
        //Check if the user has access to a specific media type.
        foreach(array_keys($this->mediaClasses) as $class)
        {
            if(Yii::$app->user->can('Medium-'.$class.'-view'))
            {
                return true;
            }
        }
        
        return Yii::$app->user->can('Medium-view') || parent::checkAccess();
    }
}
