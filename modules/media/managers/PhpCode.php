<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\managers;

use \Yii;

/** 
 * Manages a CustomField of type phpCode.
 * */
class PhpCode extends \yiingine\modules\customFields\managers\Base
{                        
    /**
     * @inheritdoc
     */
    public function rules()
    {
        // If we are not on the admin side.
        if( !CONSOLE && (
            Yii::$app->controller->getSide() !== \yiingine\web\Controller::ADMIN || 
            Yii::$app->controller->adminDisplayMode < \yiingine\models\admin\AdminParameters::ADVANCED_DISPLAY_MODE ||
            !Yii::$app->user->can('Administrator'))
        )
        {
            // This field can only be used by administrators and within the admin.
            return [[$this->getAttribute(), '\yiingine\validators\UnsafeValidator']];
        }
        
        return array_merge(parent::rules(), [[$this->getAttribute(), '\yiingine\modules\media\managers\PhpCodeValidator', 'manager' => $this]]);
    }
    
    /**
     * @inheritdoc
     */
    public function render()
    {
        $model = $this->owner;
        return ($expression = $this->owner->{$this->getAttribute()})? eval($expression) : null;
    }
    
    /**
     * @inheritdoc
     */
    protected function renderInputInternal()
    {
        // If we are not on the admin side.
        if(Yii::$app->controller->getSide() !== \yiingine\web\Controller::ADMIN)
        {
            // It is way too dangerous to display this type of fields outside the admin.
            throw new \yii\base\Exception($this->getField()->typeName().' fields can only be used from within the admin!');
        }
        else if(Yii::$app->controller->adminDisplayMode < \yiingine\models\admin\AdminParameters::ADVANCED_DISPLAY_MODE)
        {
            return null; // Only display in advanced admin mode.
        }
        
        // Register code mirror assets.
        // For usage, see http://codemirror.net/doc/manual.html
        /**$url = Yii::$app->assetManager->publish(Yii::getAlias('@yiingine/vendor/codemirror'))[1];
        Yii::$app->view->registerJsFile($url.'/lib/codemirror.js');
        Yii::$app->view->registerCssFile($url.'/lib/codemirror.css');
        // The PHP mode depends on the c-like mode.
        Yii::$app->view->registerJsFile($url.'/mode/clike/clike.js');
        Yii::$app->view->registerJsFile($url.'/mode/php/php.js');
        Yii::$app->view->registerCssFile($url.'/theme/mbo.css');*/
        
        \yiingine\assets\admin\CodeMirrorAsset::register(Yii::$app->view);
        
        // Fix a bug where the z-index of code mirror is greater than 0.
        Yii::$app->view->registerCss('.CodeMirror{z-index: 0;width:570px;height:400px;}');
        
        $name = $this->getAttribute();
        
        // Initializes the coding interface.
        Yii::$app->view->registerJs('
            var cMGroupTitle_'.$name.' = $("#'.\yii\helpers\Html::getInputId($this->owner, $name).'").parent().prev();
            var cMGroupTitleClosing_'.$name.' = false ;
            if(cMGroupTitle_'.$this->field->name.'.find(".openCloseBtn").hasClass("fa-plus-circle"))
            {
                cMGroupTitle_'.$name.'.trigger("click");
                cMGroupTitleClosing_'.$name.' = true;
            }
            var codeMirror_'.$name.' = CodeMirror.fromTextArea($("#'.\yii\helpers\Html::getInputId($this->owner, $name).'")[0],{
                theme: "mbo",
                lineNumbers: true,
                indentUnit: 4
            });
                
            // Necessary due to bug #1991.
            codeMirror_'.$name.'.refresh();
                
            if(cMGroupTitleClosing_'.$name.')
            {
                cMGroupTitle_'.$name.'.trigger("click");
                // Necessary due to bug #1991.
                //codeMirror_'.$name.'.refresh();
            }
        ', \yii\web\View::POS_READY);
        
        return [
            'type' => 'textarea',
            'style' => 'width:560px;',
            'row' => 50
        ];
    }
}

/**
 * A validator for validating php code fields.
 * */
class PhpCodeValidator extends \yii\validators\Validator
{
    /** @var PhpCode the manager for the field to validate.*/
    public $manager;

    /** @var integer the number of times validateField() was called. */
    private $_called = 0;
    
    /**
     * @inheritdoc
     * */
    public function validateAttribute($model, $attribute)
    {
        if(CONSOLE) // No validation is done in CONSOLE mode as required components like View or Request might be missing.
        {
            return;
        }
    
        $model->$attribute = rtrim($model->$attribute);
    
        $this->_called++;
        /*This method cannot be called more than once per execution otherwise
         * symbols may get redeclared.*/
        if($this->_called > 1)
        {
            return;
        }
    
        try // Attempt to execute the code.
        {
            $this->manager->render();
        }
        catch (\yii\base\Exception $e)
        {
            // HTTP exception are deliberatly thrown so not considered errors.
            if($e instanceof \yii\web\HttpException)
            {
                return;
            }
    
            $model->addError($attribute, $e->getMessage());
        }
    }
}
