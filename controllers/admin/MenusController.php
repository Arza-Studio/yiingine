<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\controllers\admin;

/**
* The admin controller for the MenuItem model.
*/
class MenusController extends \yiingine\web\admin\ModelController
{    
    /**
    * @inheritdoc
    */
    public function model(){ return new \yiingine\models\MenuItem(); }
}
