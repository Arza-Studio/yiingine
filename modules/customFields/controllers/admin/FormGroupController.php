<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\customFields\controllers\admin;

use \yiingine\modules\customFields\models\FormGroup;

/** 
 * The admin controller for the FormGroup model.
 * */
class FormGroupController extends \yiingine\web\admin\ModelController 
{   
    /**
     * @inheritdoc
     * */
    public function model()
    {
        /**
         * Override of parent implementation to set the owner of the FormGroup.
         * */
        
        FormGroup::$customFieldsModule = $this->module;
        $model = parent::model();
        $model->owner = $this->module->tableName;
        return $model;
    }
}
