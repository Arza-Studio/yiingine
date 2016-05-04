<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\controllers\admin;

use \Yii;

/**
* @desc The admin controller for the ConfigEntry model.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class ConfigEntryController extends \yiingine\web\admin\ModelController
{    
    /**
    * @inheritdoc
    */
    public function model()
    { 
        return new \yiingine\models\ConfigEntry(); 
    }
}
