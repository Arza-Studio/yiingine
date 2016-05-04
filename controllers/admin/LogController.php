<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\controllers\admin;

/**
* A controller for browsing through active record changelog entries.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class LogController extends \yiingine\web\admin\ModelController
{    
    /**
    * @inheritdoc
    */
    public function model(){ return new \yiingine\models\ActiveRecordLogEntry(); }
    
    /**
    * @inheritdoc
    */
    public function accessRules()
    {
        return array_merge([
            [ // Cannot modify log entries.
                'allow' => false,
                'actions' => ['delete', 'create', 'update'],
            ]
        ], parent::accessRules());
    }
}
