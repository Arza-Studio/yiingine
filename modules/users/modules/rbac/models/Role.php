<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\models;

/**
 * This is the model class for a role.
 */
class Role extends AuthorizationItem
{    
    /**
     * Returns a human readable name for the model. Actually not legal in PHP and in OO
     * (static belongs to a class) but an indication that this static method is available.
     * @param $plural boolean if the label should be plural
     * @return string the model's name
     */
    public static function getModelLabel($plural = false)
    {
        return \Yii::t(__CLASS__, '{n, plural, =1{Role}other{Roles}}', ['n' => $plural ? 2 : 1]);
    }
    
    /** @return integer the type of the item.*/
    public function getType()
    {
        return AuthorizationItem::ROLE;
    }
}
